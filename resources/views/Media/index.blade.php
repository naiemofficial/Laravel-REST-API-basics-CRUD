<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        {{ class_basename($model) }}
    </title>
    <style>
        img {
            max-width: 100px;
        }
        th, td {
            text-align: center;
            padding: 5px;;
            vertical-align: top;
        }
    </style>
</head>
<body>

    <h1>Media List</h1>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
            </tr>
        </thead>
        <tbody>
            @foreach($medias as $media)
                <tr>
                    <td>{{ $media->id }}</td>
                    <td><img src="{{ ($media_url)($media->name) }}"/></td>
                    <td>{{ $media->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>