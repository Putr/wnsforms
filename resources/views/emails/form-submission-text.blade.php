New Form Submission: {{ $form->name }}

Form: {{ $form->name }}
Submitted at: {{ $submittedAt }}

Form Data:
@foreach($data as $key => $value)
{{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}
@endforeach

---
This is an automated email sent by your WNSForms application.
IP Address: {{ $submission->ip_address }}
@if($submission->referrer)
Referrer: {{ $submission->referrer }}
@endif
