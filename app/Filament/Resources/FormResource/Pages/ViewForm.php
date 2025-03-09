<?php

namespace App\Filament\Resources\FormResource\Pages;

use App\Filament\Resources\FormResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Filament\Notifications\Notification;

class ViewForm extends ViewRecord
{
    protected static string $resource = FormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('copyInstallationCode')
                ->label('Copy HTML Form')
                ->icon('heroicon-o-clipboard-document')
                ->action(function () {
                    $formUrl = url("/api/post/{$this->record->hash}");
                    $slackNote = $this->record->slack_webhook_url
                        ? "\n<!-- This form is integrated with Slack. Submissions will be sent to your Slack channel. -->"
                        : "\n<!-- To enable Slack integration, add a Slack webhook URL in the form settings. -->";

                    $csrfNote = "\n<!-- CSRF protection is disabled for this form to allow cross-domain submissions. The form submission endpoint is defined in the API routes. -->";

                    $htmlCode = "<form action=\"{$formUrl}\" method=\"POST\">{$csrfNote}{$slackNote}
    <!-- Form fields -->
    <div>
        <label for=\"name\">Name</label>
        <input type=\"text\" name=\"name\" id=\"name\" required>
    </div>
    <div>
        <label for=\"email\">Email</label>
        <input type=\"email\" name=\"email\" id=\"email\" required>
    </div>
    <div>
        <label for=\"message\">Message</label>
        <textarea name=\"message\" id=\"message\" required></textarea>
    </div>

    <!-- Honeypot field to prevent spam -->
    <div style=\"display: none;\">
        <label for=\"website\">Website</label>
        <input type=\"text\" name=\"website\" id=\"website\">
    </div>

    <button type=\"submit\">Submit</button>
</form>

<!-- JavaScript version (for handling submissions without redirects) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                // No CSRF token needed - CSRF protection is disabled for this endpoint
            }
        })
        .then(response => response.json())
        .then(data => {
            alert('Form submitted successfully!');
            form.reset();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('There was an error submitting the form. Please try again.');
        });
    });
});
</script>";

                    Notification::make()
                        ->title('HTML form code copied to clipboard!')
                        ->success()
                        ->send();

                    $this->js("navigator.clipboard.writeText(`{$htmlCode}`);");
                }),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
