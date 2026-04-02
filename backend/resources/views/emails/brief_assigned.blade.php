<!DOCTYPE html>
<html>
<head>
    <title>Nouveau Brief Assigné</title>
</head>
<body>
    <h1>Bonjour !</h1>
    <p>Un nouveau brief vient de vous être assigné : <strong>{{ $brief->title }}</strong>.</p>
    <p>Description : {{ $brief->description }}</p>
    <p>Date de début : {{ $brief->date_start->format('d/m/Y') }}</p>
    <p>Date de fin : {{ $brief->date_end->format('d/m/Y') }}</p>
    <p>Bon courage !</p>
</body>
</html>
