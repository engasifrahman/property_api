<x-mail::message>
Hello {{$user->name}}!<br>
Your bulk property upload is under process.<br>
@if($batch_id)
Currently, {{$total_batch_jobs}} job is being procced by the system and current status is {{$batch_status}}

<x-mail::button :url="url('/api/v1/batch-info/'. $batch_id)">
Live Status
</x-mail::button>
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
