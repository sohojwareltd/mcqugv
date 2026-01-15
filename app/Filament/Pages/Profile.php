<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;

class Profile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static string $view = 'filament.pages.profile';

    protected static ?string $navigationLabel = 'My Profile';

    protected static ?string $navigationGroup = 'Account';

    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(auth()->user()->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Profile Information')
                    ->description('Update your account profile information and email address.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpan(1),
                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At')
                            ->disabled()
                            ->displayFormat('d/m/Y H:i')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Change Password')
                    ->description('Enter a new password to change your current password. Leave blank to keep current password.')
                    ->schema([
                        Forms\Components\TextInput::make('current_password')
                            ->label('Current Password')
                            ->password()
                            ->required(fn ($get) => filled($get('new_password')))
                            ->dehydrated(false)
                            ->helperText('Required if you want to change your password')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('new_password')
                            ->label('New Password')
                            ->password()
                            ->minLength(8)
                            ->dehydrated(false)
                            ->helperText('Leave blank to keep current password')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('new_password_confirmation')
                            ->label('Confirm New Password')
                            ->password()
                            ->same('new_password')
                            ->dehydrated(false)
                            ->required(fn ($get) => filled($get('new_password')))
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ])
            ->statePath('data')
            ->model(auth()->user());
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $user = auth()->user();

        // Validate current password if new password is provided
        if (!empty($data['new_password'])) {
            if (empty($data['current_password']) || !Hash::check($data['current_password'], $user->password)) {
                Notification::make()
                    ->title('Password Update Failed')
                    ->body('Current password is incorrect.')
                    ->danger()
                    ->send();
                return;
            }

            // Update password
            $user->password = Hash::make($data['new_password']);
        }

        // Update other fields
        $user->name = $data['name'];
        $user->email = $data['email'];

        $user->save();

        Notification::make()
            ->title('Profile Updated')
            ->body('Your profile has been updated successfully.')
            ->success()
            ->send();

        // Refresh form
        $this->form->fill($user->fresh()->toArray());
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('save')
                ->label('Save Changes')
                ->submit('save'),
        ];
    }
}
