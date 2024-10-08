<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        {{ class_basename($model) }}
    </title>
</head>
<body>

    <h1>Student List</h1>
    <table border="1">
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Student ID</th>
                <th>Student Email</th>
                <th>Student Phone</th>
                <th>Address</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
                <tr>
                    <td>{{ $student->name }}</td>
                    <td>{{ $student->uid }}</td>
                    <td>{{ $student->email }}</td>
                    <td>{{ $student->phone }}</td>
                    <td>{{ $student->address }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>