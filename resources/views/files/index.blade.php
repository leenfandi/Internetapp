<!DOCTYPE html>
<html>
<head>
    <title>File Upload and Deletion</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h1 {
            margin-bottom: 20px;
        }

        form {
            margin-bottom: 20px;
        }

        input[type="file"] {
            margin-right: 10px;
        }

        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .message {
            margin-top: 10px;
        }

        .error {
            color: #f00;
        }
    </style>
</head>
<body>
    <h1>File Upload</h1>
    <form action="{{ route('file.upload') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file">
        <button type="submit">Upload</button>
    </form>

    <h1>File Deletion</h1>
    <form action="{{ route('file.delete', ['fileName' => 'example.txt']) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit">Delete</button>
    </form>

    @if(session('success'))
        <div class="message">
            <strong>Success:</strong> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="message error">
            <strong>Error:</strong> {{ session('error') }}
        </div>
    @endif

    <!-- Add this section to display the uploaded files -->
    <h1>Uploaded Files</h1>
    @foreach($files as $file)
        <p>{{ $file }}</p>
        <form action="{{ route('file.delete', ['fileName' => $file]) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit">Delete</button>
        </form>
    @endforeach
</body>
</html>
