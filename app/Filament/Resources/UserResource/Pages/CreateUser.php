<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Settings\MailSettings;
use Exception;
use Filament\Facades\Filament;
use Filament\Notifications\Auth\VerifyEmail;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Jika toggle verify_email_now di-check, set email_verified_at
        if (isset($data['verify_email_now']) && $data['verify_email_now']) {
            $data['email_verified_at'] = now();
        } elseif (!isset($data['email_verified_at'])) {
            // Jika tidak di-set, tetap null
            $data['email_verified_at'] = null;
        }
        
        // Hapus verify_email_now dari data karena tidak ada di database
        unset($data['verify_email_now']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $user = $this->record;
        
        // Hanya kirim email verifikasi jika email belum terverifikasi
        if ($user->email_verified_at === null) {
            $settings = app(MailSettings::class);

            if (! method_exists($user, 'notify')) {
                $userClass = $user::class;

                throw new Exception("Model [{$userClass}] does not have a [notify()] method.");
            }

            $notification = new VerifyEmail();
            $notification->url = Filament::getVerifyEmailUrl($user);

            $settings->loadMailSettingsToConfig();

            $user->notify($notification);

            Notification::make()
                ->title(__('resource.user.notifications.notification_resent.title'))
                ->success()
                ->send();
        }
    }
}
