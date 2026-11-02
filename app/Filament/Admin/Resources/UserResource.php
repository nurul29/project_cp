<?php

namespace App\Filament\Admin\Resources;

use App\Enums\UserRole;
use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nama lengkap')
                ->required()
                ->maxLength(255)
                ->helperText('Nama lengkap beserta gelar (untuk guru) atau nama lengkap siswa sesuai data sekolah.'),

            Forms\Components\Select::make('role')
                ->label('Peran akun')
                ->options([
                    UserRole::Student->value => 'Siswa',
                    UserRole::Teacher->value => 'Guru',
                    UserRole::Admin->value => 'Admin',
                ])
                ->required()
                ->live()
                ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state): void {
                    if ($state !== UserRole::Student->value && $state !== 'student') {
                        $set('classroom_id', null);
                    }
                    // Reset password fields saat peran berubah
                    $set('password', '');
                    $set('password_confirmation', '');

                    // Jika berubah ke Siswa dan NIS sudah ada, generate ulang
                    $nis = $get('username');
                    if (($state === UserRole::Student->value || $state === 'student') && filled($nis)) {
                        $generated = static::generateStudentPassword($nis);
                        $set('password', $generated);
                        $set('password_confirmation', $generated);
                    }
                })
                ->helperText('Pilih peran: Siswa (ruang baca & tugas kelas), Guru (kelola materi & kuis), Admin (akses penuh sistem).'),

            Forms\Components\TextInput::make('username')
                ->label('NIS / NIP / Nama Pengguna')
                ->placeholder('Siswa: NIS (202601001) · Guru: NIP / username')
                ->required()
                ->maxLength(255)
                ->live(debounce: 400)
                ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state, string $operation): void {
                    // Auto-generate kata sandi standar untuk Siswa saat NIS diisi pada halaman Buat Pengguna
                    $role = $get('role');
                    if ($operation === 'create' && ($role === UserRole::Student->value || $role === 'student') && filled($state)) {
                        $generated = static::generateStudentPassword($state);
                        $set('password', $generated);
                        $set('password_confirmation', $generated);
                    }
                })
                ->unique(ignoreRecord: true)
                ->validationMessages([
                    'unique' => 'NIS / Nama Pengguna ini sudah terdaftar pada akun lain. Mohon gunakan yang berbeda untuk mencegah duplikasi akun.',
                ])
                ->helperText('Siswa: isi NIS (dipakai sebagai ID login & otomatis men-generate kata sandi). Guru: isi NIP atau username.'),

            Forms\Components\TextInput::make('email')
                ->label('Email pengguna (Opsional)')
                ->email()
                ->nullable()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->validationMessages([
                    'unique' => 'Email ini sudah digunakan oleh akun lain. Satu email hanya boleh terdaftar pada satu akun.',
                ])
                ->helperText('Opsional. Jika diisi harus unik. Pengguna dapat melengkapi sendiri via menu Profil.'),

            Forms\Components\Select::make('classroom_id')
                ->label('Kelas siswa')
                ->relationship('classroom', 'name', fn ($query) => $query->ordered())
                ->searchable()
                ->preload()
                ->visible(fn (Forms\Get $get): bool => $get('role') === UserRole::Student->value || $get('role') === 'student')
                ->helperText('Rombongan belajar aktif siswa pada tahun ajaran ini.'),

            Forms\Components\Select::make('teaching_subject_ids')
                ->label('Mata pelajaran yang diampu')
                ->options(fn () => \App\Models\Subject::orderBy('code')->pluck('name', 'id'))
                ->multiple()
                ->visible(fn (Forms\Get $get): bool => $get('role') === UserRole::Teacher->value || $get('role') === 'teacher')
                ->dehydrated(false)
                ->helperText('Dapat memilih lebih dari satu mata pelajaran (misal: Matematika & Bahasa Indonesia).'),

            Forms\Components\Select::make('teaching_classroom_ids')
                ->label('Kelas yang diampu')
                ->options(fn () => \App\Models\Classroom::ordered()->pluck('name', 'id'))
                ->multiple()
                ->visible(fn (Forms\Get $get): bool => $get('role') === UserRole::Teacher->value || $get('role') === 'teacher')
                ->dehydrated(false)
                ->helperText('Pilih kelas yang diampu guru ini (misal: VII A, VIII A). Kelas yang sudah diampu guru lain pada mapel yang sama tidak akan tertimpa.'),

            Forms\Components\TextInput::make('password')
                ->label(fn (string $operation): string => $operation === 'create' ? 'Kata sandi awal' : 'Kata sandi baru')
                ->password()
                ->revealable()
                ->required(fn (Forms\Get $get, string $operation): bool =>
                    $operation === 'create' && ! ($get('role') === UserRole::Student->value || $get('role') === 'student')
                )
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                ->rule(\Illuminate\Validation\Rules\Password::min(8)->symbols())
                ->placeholder(fn (Forms\Get $get, string $operation): string =>
                    $operation === 'create' && ($get('role') === UserRole::Student->value || $get('role') === 'student')
                        ? 'Otomatis: 7 digit terakhir NIS + @'
                        : ''
                )
                ->suffixAction(
                    Forms\Components\Actions\Action::make('generateFromUsername')
                        ->icon('heroicon-m-key')
                        ->tooltip('Generate kata sandi standar (7 karakter terakhir NIS/NIP + @)')
                        ->action(function (Forms\Set $set, Forms\Get $get): void {
                            $nis = (string) $get('username');
                            if (filled($nis)) {
                                $generated = static::generateStudentPassword($nis);
                                $set('password', $generated);
                                $set('password_confirmation', $generated);
                            }
                        })
                )
                ->helperText(fn (Forms\Get $get, string $operation): string =>
                    $operation === 'create' && ($get('role') === UserRole::Student->value || $get('role') === 'student')
                        ? '🔑 Otomatis ter-generate dari NIS (7 digit terakhir + "@"). Jika dikosongkan pun sistem otomatis mengisinya saat disimpan.'
                        : 'Minimal 8 karakter dan wajib menyertakan minimal 1 simbol (!@#$%^&* dll). Klik ikon kunci 🔑 untuk generate dari NIP.'
                ),

            Forms\Components\TextInput::make('password_confirmation')
                ->label('Konfirmasi kata sandi')
                ->password()
                ->revealable()
                ->required(fn (Forms\Get $get): bool => filled($get('password')))
                ->same('password')
                ->dehydrated(false)
                ->placeholder(fn (Forms\Get $get, string $operation): string =>
                    $operation === 'create' && ($get('role') === UserRole::Student->value || $get('role') === 'student')
                        ? 'Otomatis mengikuti kata sandi di atas'
                        : ''
                )
                ->helperText(fn (Forms\Get $get, string $operation): string =>
                    $operation === 'create' && ($get('role') === UserRole::Student->value || $get('role') === 'student')
                        ? 'Terisi otomatis saat NIS diketik.'
                        : 'Ketik ulang kata sandi yang sama persis untuk verifikasi kecocokan.'
                ),

            Forms\Components\Toggle::make('is_active')
                ->label('Akun aktif')
                ->default(true)
                ->required()
                ->helperText('Centang untuk mengizinkan pengguna masuk. Jika dinonaktifkan, akun tidak dapat masuk namun seluruh riwayat data tetap aman.'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('username')->label('Nama pengguna')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->formatStateUsing(fn (UserRole $state) => match ($state) {
                        UserRole::Student => 'Siswa',
                        UserRole::Teacher => 'Guru',
                        UserRole::Admin => 'Admin',
                    })
                    ->color(fn (UserRole $state) => match ($state) {
                        UserRole::Student => 'info',
                        UserRole::Teacher => 'success',
                        UserRole::Admin => 'warning',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('classroom.name')->label('Kelas')->placeholder('—')->toggleable(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Peran')
                    ->options([
                        UserRole::Student->value => 'Siswa',
                        UserRole::Teacher->value => 'Guru',
                        UserRole::Admin->value => 'Admin',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')->label('Status aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('resetPassword')
                    ->label('Atur kata sandi')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->form(function (User $record): array {
                        $clean = trim($record->username);
                        $suffix = strlen($clean) >= 7 ? substr($clean, -7) : str_pad($clean, 7, '0', STR_PAD_LEFT);
                        $defaultPassword = $suffix . '@';

                        return [
                            Forms\Components\Placeholder::make('target_user')
                                ->label('Pengguna')
                                ->content("{$record->name} ({$record->username}) • Peran: " . ($record->isStudent() ? 'Siswa' : ($record->isTeacher() ? 'Guru' : 'Admin'))),
                            Forms\Components\TextInput::make('password')
                                ->label('Kata sandi baru')
                                ->password()
                                ->revealable()
                                ->default($defaultPassword)
                                ->required()
                                ->minLength(8)
                                ->rule(\Illuminate\Validation\Rules\Password::min(8)->symbols())
                                ->helperText("Standar Sekolah: 7 digit terakhir NIS/NIP + '@' (Tersedia default: {$defaultPassword})."),
                            Forms\Components\TextInput::make('password_confirmation')
                                ->label('Konfirmasi kata sandi')
                                ->password()
                                ->revealable()
                                ->default($defaultPassword)
                                ->required()
                                ->same('password')
                                ->dehydrated(false)
                                ->helperText('Ketik ulang kata sandi yang sama persis untuk verifikasi.'),
                        ];
                    })
                    ->action(function (User $record, array $data): void {
                        $record->update(['password' => Hash::make($data['password'])]);
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Kata sandi berhasil diperbarui')
                            ->body("Kata sandi akun {$record->name} telah berhasil direset.")
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->successNotificationTitle('Pengguna berhasil dihapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with('classroom');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    /**
     * Generate kata sandi standar sekolah dari NIS/NIP/Username.
     * Formula: 7 karakter terakhir identifier + '@'
     * Contoh: NIS "202601001" → "2601001@"
     */
    public static function generateStudentPassword(string $identifier): string
    {
        $clean = trim($identifier);
        $suffix = strlen($clean) >= 7
            ? substr($clean, -7)
            : str_pad($clean, 7, '0', STR_PAD_LEFT);

        return $suffix . '@';
    }
}
