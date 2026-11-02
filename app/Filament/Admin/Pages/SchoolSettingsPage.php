<?php

namespace App\Filament\Admin\Pages;

use App\Models\SchoolSetting;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SchoolSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Pengaturan sekolah';

    protected static ?string $title = 'Pengaturan sekolah';

    protected static ?string $slug = 'school-settings';

    protected static ?int $navigationSort = 8;

    protected static string $view = 'filament.admin.pages.school-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(SchoolSetting::current()->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('school_name')->label('Nama sekolah')->required()->maxLength(255),
                TextInput::make('school_year')->label('Tahun ajaran')->required()->maxLength(20),
                Select::make('semester')->label('Semester')->options([
                    'Ganjil' => 'Ganjil',
                    'Genap' => 'Genap',
                ])->required(),
            ])
            ->statePath('data')
            ->columns(2);
    }

    public function save(): void
    {
        SchoolSetting::current()->fill($this->form->getState())->save();

        Notification::make()
            ->title('Pengaturan sekolah berhasil disimpan')
            ->success()
            ->send();
    }
}
