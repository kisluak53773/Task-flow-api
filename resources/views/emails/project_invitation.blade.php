<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Project Invitation</title>
</head>
<body style="font-family: sans-serif; padding: 20px; color: #333;">
<h2>Hello, {{ $user->name }}!</h2>
<p>You have been invited to join the project <strong>{{ $project->name }}</strong> as a team contributor.</p>

@if($project->description)
    <p style="color: #666; font-style: italic;">Project Overview: "{{ $project->description }}"</p>
@endif

<p>Log in to your TaskFlow dashboard to view the project workspace and begin tracking your assignments.</p>
<p>Best regards,<br>The TaskFlow Team</p>
</body>
</html>
