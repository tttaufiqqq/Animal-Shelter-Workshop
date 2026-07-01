<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Animal - Stray Animals Shelter</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Simple fade-in animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.4s ease-out;
        }

        /* Image preview styles */
        .image-preview {
            position: relative;
            transition: transform 0.2s;
        }

        .image-preview:hover {
            transform: scale(1.05);
        }

        /* Progress bar */
        .progress-step {
            transition: all 0.3s ease;
        }

        .progress-step.active {
            background: linear-gradient(135deg, #9333ea 0%, #7e22ce 100%);
            color: white;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 to-indigo-50 min-h-screen">

@include('navbar')

<div class="container mx-auto px-4 py-8 max-w-4xl">
