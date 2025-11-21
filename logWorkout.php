<?php 
session_start();

require('connect-db.php'); 
require('request-db.php');


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_data = getNameByID($_SESSION['user_id']);
$username = $user_data ? htmlspecialchars($user_data['user_name']) : 'Guest';

$log_message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logWorkoutBtn'])) {
    $workout = $_POST['workout_select'] ?? '';
    $metric = $_POST['metric_input'] ?? '';
    
    if (!empty($workout) && !empty($metric)) {
        $log_message = "<span class='text-green-500'>Successfully logged: $workout with metric '$metric'.</span>";
    } else {
        $log_message = "<span class='text-red-500'>Please select a workout and enter a metric.</span>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"> 
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log Workout</title>
    <meta name="author" content="Alston Hou, Adnan Murtaza, Ben Chang, Kenny Nguyen">
    <meta name="description" content="This web app is designed to help UVA student's track their workouts! It offers competitive features with friends to keep users motivated.">
    <meta name="keywords" content="UVA, Workout, Fitness">
    <link rel="icon" type="image/png" href="https://www.cs.virginia.edu/~up3f/cs4750/images/db-icon.png" />

    <script src="https://cdn.tailwindcss.com"></script>
     <style>
        /* Custom styles to match the dark blue boxes */
         .card-bg-dark {
             background-color: #355375; /* A deep, slate blue */
        }
        .page-text {
            color: #1a1a1a;
        }
        /* Style for the button appearance (mimicking the dashboard style) */
        .log-button {
            padding: 0.75rem 1rem;
            border: 2px solid #fff;
            color: #1a1a1a;
            font-weight: 600;
            background-color: #f7f7f7;
            border-radius: 0.375rem; /* rounded-md */
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.15s;
        }
        .log-button:hover {
            background-color: #e5e7eb;
        }
    </style>
</head>
<body class="bg-white min-h-screen p-4 sm:p-8 font-sans">
    <div class="max-w-7xl mx-auto">
    <header class="mb-8">
            <h1 class="text-5xl font-extrabold page-text">Good Day, <?php echo $username; ?></h1>
            <p class="text-lg text-gray-700 mt-1">Track your daily activity</p>
        </header>
        <div class="flex flex-col lg:flex-row gap-8">
            <nav class="lg:w-1/5">
                <h2 class="text-xl font-bold page-text mb-4">Main Menu</h2>
                <ul class="space-y-3 text-gray-500 font-medium">
                    <li><a href="dashboard.php" class="hover:text-black">Dashboard</a></li>
                    <li><a href="log_workout.php" class="text-black font-extrabold text-xl border-b-2 border-black">Log Workout</a></li>
                    <li><a href="group.php" class="hover:text-black">Group Workout</a></li>
                    <li class="pt-4"><a href="login.php" class="text-red-500 hover:text-red-700">Logout</a></li>
                </ul>
            </nav>

            <main class="lg:w-4/5 flex justify-start">
                <div class="w-full max-w-sm card-bg-dark p-6 rounded-lg shadow-xl text-white">
                    <h2 class="text-2xl font-semibold mb-6 text-center">Log Workout</h2>
                    
                    <?php if (!empty($log_message)): ?>
                        <div class="mb-4 text-center text-sm font-bold">
                            <?php echo $log_message; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="log_workout.php" class="space-y-4">
                        
                        <div class="relative mb-3">
                            <select name="workout_select" class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner appearance-none pr-8 focus:outline-none">
                                <option value="">Select Workout</option>
                                <option value="Running">Running (Distance)</option>
                                <option value="Lifting">Weight Lifting (Sets/Reps)</option>
                                <option value="Cardio">Cardio (Time)</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                <svg class="w-4 h-4 text-black fill-current" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                            </div>
                        </div>

                        <div class="relative mb-6">
                             <input 
                                type="text" 
                                name="metric_input" 
                                placeholder="Metric to Record" 
                                class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner focus:outline-none"
                            >
                        </div>

                        <button 
                            type="submit" 
                            name="logWorkoutBtn"
                            class="log-button w-full bg-white hover:bg-gray-200 text-black border-black mb-3"
                        >
                            Log
                        </button>
                        
                        <button 
                            type="button" 
                            onclick="alert('Delete functionality not yet implemented.')"
                            class="log-button w-full bg-white hover:bg-gray-200 text-black border-black"
                        >
                            Delete
                        </button>

                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>