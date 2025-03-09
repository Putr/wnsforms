<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormResource\Pages;
use App\Filament\Resources\FormResource\RelationManagers;
use App\Models\Form as FormModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FormResource extends Resource
{
    protected static ?string $model = FormModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Forms';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('hash')
                    ->maxLength(255)
                    ->helperText('Leave empty to auto-generate')
                    ->placeholder('Auto-generated if left empty'),
                Forms\Components\TextInput::make('allowed_domains')
                    ->helperText('Enter domains separated by commas (e.g., example.com,test.com)')
                    ->placeholder('Leave empty to allow all domains'),
                Forms\Components\TextInput::make('notification_email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('success_redirect')
                    ->label('Success Redirect URL')
                    ->url()
                    ->helperText('Where to redirect after successful form submission (optional)')
                    ->placeholder('https://example.com/thank-you')
                    ->maxLength(255),
                Forms\Components\TextInput::make('error_redirect')
                    ->label('Error Redirect URL')
                    ->url()
                    ->helperText('Where to redirect if there is an error (optional)')
                    ->placeholder('https://example.com/error')
                    ->maxLength(255),
                Forms\Components\TextInput::make('slack_webhook_url')
                    ->label('Slack Webhook URL')
                    ->url()
                    ->helperText('Slack webhook URL to send form submissions to (optional)')
                    ->placeholder('https://hooks.slack.com/services/XXX/YYY/ZZZ')
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('hash')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('notification_email')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ])
                    ->label('Status'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Form Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('name'),
                        Infolists\Components\TextEntry::make('hash')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('allowed_domains'),
                        Infolists\Components\TextEntry::make('notification_email'),
                        Infolists\Components\TextEntry::make('success_redirect')
                            ->label('Success Redirect')
                            ->url(fn(FormModel $record): ?string => $record->success_redirect),
                        Infolists\Components\TextEntry::make('error_redirect')
                            ->label('Error Redirect')
                            ->url(fn(FormModel $record): ?string => $record->error_redirect),
                        Infolists\Components\TextEntry::make('slack_webhook_url')
                            ->label('Slack Webhook')
                            ->url(fn(FormModel $record): ?string => $record->slack_webhook_url),
                        Infolists\Components\IconEntry::make('is_active')
                            ->boolean(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Slack Integration')
                    ->schema([
                        Infolists\Components\TextEntry::make('slack_instructions')
                            ->label('How to Set Up Slack Integration')
                            ->html()
                            ->state(function (FormModel $record): string {
                                return "
                                <div class='space-y-4'>
                                    <p>To set up Slack integration for this form, follow these steps:</p>

                                    <ol class='list-decimal pl-5 space-y-2'>
                                        <li>Go to your Slack workspace</li>
                                        <li>Create a new Slack app at <a href='https://api.slack.com/apps' target='_blank' class='text-blue-600 hover:underline'>https://api.slack.com/apps</a></li>
                                        <li>Give your app a name (e.g., 'Form Notifications')</li>
                                        <li>Select the workspace where you want to receive notifications</li>
                                        <li>Once created, go to 'Incoming Webhooks' in the sidebar</li>
                                        <li>Toggle 'Activate Incoming Webhooks' to On</li>
                                        <li>Click 'Add New Webhook to Workspace'</li>
                                        <li>Select the channel where you want to receive notifications</li>
                                        <li>Copy the Webhook URL</li>
                                        <li>Paste the URL in the 'Slack Webhook URL' field in the form settings</li>
                                    </ol>

                                    <div class='mt-4 p-4 bg-blue-50 rounded-lg'>
                                        <h4 class='font-medium text-blue-800'>Current Status</h4>
                                        " . ($record->slack_webhook_url
                                    ? "<p class='text-green-600'>✅ Slack integration is configured</p>"
                                    : "<p class='text-red-600'>❌ Slack integration is not configured</p>") . "
                                    </div>
                                </div>
                                ";
                            }),
                    ]),

                Infolists\Components\Section::make('How to Install')
                    ->schema([
                        Infolists\Components\TextEntry::make('installation_html')
                            ->label('Installation Instructions')
                            ->html()
                            ->state(function (FormModel $record): string {
                                $formUrl = url("/api/post/{$record->hash}");

                                return "
                                <div class='space-y-4'>
                                    <div>
                                        <h3 class='text-lg font-medium'>Basic HTML Form</h3>
                                        <p class='mt-2'>Copy and paste this HTML form into your website:</p>
                                        <pre class='mt-2 p-4 bg-gray-100 rounded-lg overflow-x-auto text-sm'>&lt;form action=&quot;{$formUrl}&quot; method=&quot;POST&quot;&gt;
    &lt;!-- Form fields --&gt;
    &lt;div&gt;
        &lt;label for=&quot;name&quot;&gt;Name&lt;/label&gt;
        &lt;input type=&quot;text&quot; name=&quot;name&quot; id=&quot;name&quot; required&gt;
    &lt;/div&gt;
    &lt;div&gt;
        &lt;label for=&quot;email&quot;&gt;Email&lt;/label&gt;
        &lt;input type=&quot;email&quot; name=&quot;email&quot; id=&quot;email&quot; required&gt;
    &lt;/div&gt;
    &lt;div&gt;
        &lt;label for=&quot;message&quot;&gt;Message&lt;/label&gt;
        &lt;textarea name=&quot;message&quot; id=&quot;message&quot; required&gt;&lt;/textarea&gt;
    &lt;/div&gt;

    &lt;!-- Honeypot field to prevent spam --&gt;
    &lt;div style=&quot;display: none;&quot;&gt;
        &lt;label for=&quot;website&quot;&gt;Website&lt;/label&gt;
        &lt;input type=&quot;text&quot; name=&quot;website&quot; id=&quot;website&quot;&gt;
    &lt;/div&gt;

    &lt;button type=&quot;submit&quot;&gt;Submit&lt;/button&gt;
&lt;/form&gt;</pre>
                                    </div>

                                    <div>
                                        <h3 class='text-lg font-medium'>JavaScript Version</h3>
                                        <p class='mt-2'>If you prefer to handle the form submission with JavaScript:</p>
                                        <pre class='mt-2 p-4 bg-gray-100 rounded-lg overflow-x-auto text-sm'>&lt;form id=&quot;my-form&quot;&gt;
    &lt;!-- Form fields --&gt;
    &lt;div&gt;
        &lt;label for=&quot;name&quot;&gt;Name&lt;/label&gt;
        &lt;input type=&quot;text&quot; name=&quot;name&quot; id=&quot;name&quot; required&gt;
    &lt;/div&gt;
    &lt;div&gt;
        &lt;label for=&quot;email&quot;&gt;Email&lt;/label&gt;
        &lt;input type=&quot;email&quot; name=&quot;email&quot; id=&quot;email&quot; required&gt;
    &lt;/div&gt;
    &lt;div&gt;
        &lt;label for=&quot;message&quot;&gt;Message&lt;/label&gt;
        &lt;textarea name=&quot;message&quot; id=&quot;message&quot; required&gt;&lt;/textarea&gt;
    &lt;/div&gt;

    &lt;!-- Honeypot field to prevent spam --&gt;
    &lt;div style=&quot;display: none;&quot;&gt;
        &lt;label for=&quot;website&quot;&gt;Website&lt;/label&gt;
        &lt;input type=&quot;text&quot; name=&quot;website&quot; id=&quot;website&quot;&gt;
    &lt;/div&gt;

    &lt;button type=&quot;submit&quot;&gt;Submit&lt;/button&gt;
&lt;/form&gt;

&lt;script&gt;
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('my-form');
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);

        fetch('{$formUrl}', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
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
&lt;/script&gt;</pre>
                                    </div>

                                    <div>
                                        <h3 class='text-lg font-medium'>Spam Protection</h3>
                                        <p class='mt-2'>This form includes the following spam protection mechanisms:</p>
                                        <ul class='list-disc pl-5 mt-2 space-y-1'>
                                            <li><strong>Honeypot Fields:</strong> Hidden fields that bots tend to fill out but humans don't see.</li>
                                            <li><strong>Rate Limiting:</strong> Limits submissions to 10 per hour from the same IP address.</li>
                                            <li><strong>Domain Restriction:</strong> " . ($record->allowed_domains ? "Only allows submissions from: <code>{$record->allowed_domains}</code>" : "No domain restrictions (accepts submissions from any domain)") . "</li>
                                        </ul>
                                    </div>

                                    <div>
                                        <h3 class='text-lg font-medium'>CSRF Protection</h3>
                                        <p class='mt-2'>CSRF protection is <strong>disabled</strong> for this form to allow cross-domain submissions. The form submission endpoint is at <code>/api/post/{hash}</code>, which means:</p>
                                        <ul class='list-disc pl-5 mt-2 space-y-1'>
                                            <li>You don't need to include a CSRF token in your form</li>
                                            <li>The form can be submitted from any domain (unless domain restrictions are set)</li>
                                            <li>This is necessary for forms embedded on external websites</li>
                                            <li>The endpoint is outside the web middleware group, following Laravel's best practices</li>
                                        </ul>
                                    </div>

                                    <div>
                                        <h3 class='text-lg font-medium'>Redirects</h3>
                                        <p class='mt-2'>This form is configured with the following redirects:</p>
                                        <ul class='list-disc pl-5 mt-2 space-y-1'>
                                            " . ($record->success_redirect ? "<li><strong>Success Redirect:</strong> <a href='{$record->success_redirect}' target='_blank' class='text-blue-600 hover:underline'>{$record->success_redirect}</a></li>" : "<li><strong>Success Redirect:</strong> Not configured</li>") . "
                                            " . ($record->error_redirect ? "<li><strong>Error Redirect:</strong> <a href='{$record->error_redirect}' target='_blank' class='text-blue-600 hover:underline'>{$record->error_redirect}</a></li>" : "<li><strong>Error Redirect:</strong> Not configured</li>") . "
                                        </ul>
                                        <p class='mt-2'>To use redirects with HTML forms, remove the <code>Accept: application/json</code> header from your AJAX requests or don't use AJAX at all.</p>
                                    </div>

                                    <div>
                                        <h3 class='text-lg font-medium'>Slack Integration</h3>
                                        <p class='mt-2'>This form " . ($record->slack_webhook_url ? "is" : "can be") . " integrated with Slack:</p>
                                        " . ($record->slack_webhook_url
                                    ? "<p class='text-green-600 mt-2'>✅ Slack integration is configured. Form submissions will be sent to your Slack channel.</p>"
                                    : "<p class='mt-2'>To enable Slack integration, go to the form settings and add a Slack webhook URL.</p>") . "
                                    </div>

                                    <div>
                                        <h3 class='text-lg font-medium'>Notifications</h3>
                                        <p class='mt-2'>This form has the following notification methods configured:</p>
                                        <ul class='list-disc pl-5 mt-2 space-y-1'>
                                            <li><strong>Email Notifications:</strong> " . ($record->notification_email ? "Enabled - submissions will be sent to <code>{$record->notification_email}</code>" : "Not configured - add an email address in the form settings to receive email notifications") . "</li>
                                            <li><strong>Slack Notifications:</strong> " . ($record->slack_webhook_url ? "Enabled - submissions will be sent to your Slack channel" : "Not configured - add a Slack webhook URL in the form settings to receive Slack notifications") . "</li>
                                        </ul>
                                    </div>
                                </div>
                                ";
                            }),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SubmissionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListForms::route('/'),
            'create' => Pages\CreateForm::route('/create'),
            'view' => Pages\ViewForm::route('/{record}'),
            'edit' => Pages\EditForm::route('/{record}/edit'),
        ];
    }
}
