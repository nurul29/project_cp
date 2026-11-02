<?php

namespace App\Filament\Admin\Resources;

use App\Enums\AccountRequestStatus;
use App\Filament\Admin\Resources\AccountRequestResource\Pages;
use App\Models\AccountRequest;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class AccountRequestResource extends Resource
{
    protected static ?string $model = AccountRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    protected static ?string $navigationLabel = 'Permintaan akun';

    protected static ?string $modelLabel = 'Permintaan akun';

    protected static ?string $pluralModelLabel = 'Permintaan akun';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', AccountRequestStatus::Pending->value)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nama pemohon')->disabled(),
            Forms\Components\TextInput::make('username')->label('Nama pengguna')->disabled(),
            Forms\Components\TextInput::make('email')->label('Email')->disabled(),
            Forms\Components\Textarea::make('message')->label('Pesan pemohon')->disabled()->rows(3)->columnSpanFull(),
            Forms\Components\Select::make('status')
                ->label('Status')
                ->options(AccountRequestStatus::labels())
                ->required()
                ->live(),
            Forms\Components\Textarea::make('admin_notes')->label('Catatan admin')->rows(3)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Pemohon')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('username')->label('Nama pengguna')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (AccountRequestStatus $state) => $state->label())
                    ->color(fn (AccountRequestStatus $state) => match ($state) {
                        AccountRequestStatus::Pending => 'warning',
                        AccountRequestStatus::InProgress => 'info',
                        AccountRequestStatus::Resolved => 'success',
                        AccountRequestStatus::Rejected => 'danger',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('handler.name')->label('Ditangani oleh')->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')->label('Diajukan')->dateTime('d M Y H:i')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Status')->options(AccountRequestStatus::labels()),
            ])
            ->actions([
                Tables\Actions\Action::make('resetPassword')
                    ->label('Atur Sandi Standar')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->visible(fn (AccountRequest $record) => $record->status === AccountRequestStatus::Pending || $record->status === AccountRequestStatus::InProgress)
                    ->form(function (AccountRequest $record): array {
                        $user = User::where('username', $record->username)->first();
                        $defaultPassword = '';
                        if ($user) {
                            $clean = trim($user->username);
                            $suffix = strlen($clean) >= 7 ? substr($clean, -7) : str_pad($clean, 7, '0', STR_PAD_LEFT);
                            $defaultPassword = $suffix . '@';
                        } else {
                            $defaultPassword = 'sekolah123@';
                        }

                        return [
                            Forms\Components\Placeholder::make('info')
                                ->label('Akun Pemohon')
                                ->content(fn () => "Pemohon: {$record->name} (Username/NIS: {$record->username})"),
                            Forms\Components\TextInput::make('password')
                                ->label('Kata sandi baru')
                                ->password()
                                ->revealable()
                                ->default($defaultPassword)
                                ->required()
                                ->minLength(8)
                                ->rule(\Illuminate\Validation\Rules\Password::min(8)->symbols())
                                ->helperText("Formula Standar Sekolah: 7 karakter terakhir NIS/NIP + '@' (Default terisi: {$defaultPassword})."),
                            Forms\Components\TextInput::make('password_confirmation')
                                ->label('Konfirmasi kata sandi')
                                ->password()
                                ->revealable()
                                ->default($defaultPassword)
                                ->required()
                                ->same('password')
                                ->dehydrated(false)
                                ->helperText('Ketik ulang kata sandi yang sama untuk konfirmasi.'),
                        ];
                    })
                    ->action(function (AccountRequest $record, array $data): void {
                        $user = User::where('username', $record->username)->first();
                        if ($user) {
                            $user->update([
                                'password' => Hash::make($data['password']),
                            ]);
                        }

                        $record->update([
                            'status' => AccountRequestStatus::Resolved,
                            'handled_by' => auth()->id(),
                            'handled_at' => now(),
                            'admin_notes' => ($record->admin_notes ? $record->admin_notes . PHP_EOL : '') . 'Kata sandi telah direset oleh admin menggunakan format standar sekolah (' . now()->format('d/m/Y H:i') . ').',
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Kata sandi berhasil diatur dan permintaan diselesaikan')
                            ->body("Kata sandi akun {$record->name} telah diperbarui ke formula standar sekolah.")
                            ->send();
                    }),
                Tables\Actions\EditAction::make()
                    ->using(function (AccountRequest $record, array $data): AccountRequest {
                        $status = AccountRequestStatus::from($data['status']);
                        $record->update([
                            ...$data,
                            'handled_by' => auth()->id(),
                            'handled_at' => in_array($status, [AccountRequestStatus::Resolved, AccountRequestStatus::Rejected], true)
                                ? now()
                                : null,
                        ]);

                        return $record;
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountRequests::route('/'),
            'edit' => Pages\EditAccountRequest::route('/{record}/edit'),
        ];
    }
}
