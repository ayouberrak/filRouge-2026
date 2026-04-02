<!DOCTYPE html>
<html>
<head>
    <title>Nouveau Rapport Quotidien</title>
</head>
<body>
    <h1>Bonjour !</h1>
    <p>Un nouveau rapport quotidien a été soumis par <strong>{{ $report->formateur->first_name }} {{ $report->formateur->last_name }}</strong> pour la classe <strong>{{ $report->classroom->name }}</strong>.</p>
    <ul>
        <li>Date : {{ $report->date }}</li>
        <li>Nombre d'absences : {{ $report->absences_count }}</li>
        <li>Statut du brief : {{ $report->brief_status }}</li>
        <li>Notes : {{ $report->note }}</li>
    </ul>
    <p>Cordialement,</p>
</body>
</html>
