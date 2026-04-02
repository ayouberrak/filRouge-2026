<!DOCTYPE html>
<html>
<head>
    <title>Nouvelle Activité Assignée</title>
</head>
<body>
    <h1>Bonjour !</h1>
    <p>Une nouvelle activité vous a été assignée : <strong>{{ $activity->title }}</strong>.</p>
    <p>Points : {{ $activity->points }}</p>
    <p>Durée estimée : {{ $activity->duration }} minutes</p>
    <p>Travaillez bien !</p>
</body>
</html>
