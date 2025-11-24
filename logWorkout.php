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
$user_id = $_SESSION['user_id']; 

$search_results = [];
$search_term = $_POST['search_term'] ?? ''; 

// used to keep the info every time we refresh the page
$current_type = $_POST['workout_type'] ?? '';
$strength_rows = (int) ($_POST['strength_rows'] ?? 0);
$cardio_rows = (int) ($_POST['cardio_rows'] ?? 0);
$selected_workout_date = $_POST['workout_date'] ?? date('Y-m-d');
$workout_selector_value = $_POST['workoutSelector'] ?? $current_type;
$location_selector_value = $_POST['locationSelector'] ?? '';


$exercises_post = $_POST['exercise_name'] ?? [];
$sets_post = $_POST['sets'] ?? [];
$reps_post = $_POST['reps'] ?? [];
$weight_post = $_POST['weight'] ?? [];

$cardio_exercise_name_post = $_POST['cardio_exercise_name'] ?? [];
$cardio_distance_post = $_POST['cardio_distance'] ?? [];
$cardio_duration_post = $_POST['cardio_duration'] ?? [];


// statements to handle adding workout, searching, location, and date
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['logWorkoutBtn'])) {
    
    // Check if the user manually changed the type (simulated via POST)
    if (isset($_POST['workoutSelector']) && $_POST['workoutSelector'] != $current_type) {
        $current_type = $_POST['workoutSelector'];
        $strength_rows = 0;
        $cardio_rows = 0;
        $workout_selector_value = $current_type;
        $location_selector_value = $_POST['locationSelector'] ?? '';
    }
    elseif (isset($_POST['searchExerciseBtn'])) {
        $search_term = trim($_POST['search_term'] ?? '');
        if (!empty($search_term)) {
            $search_results = getExerciseByName($search_term, $current_type);
        }
        $current_type = $_POST['workout_type']; 
        $workout_selector_value = $current_type;
    }
    elseif (isset($_POST['add_selected_exercise'])) {
        $exercise_name_to_add = $_POST['exercise_name_to_add'] ?? '';
        $current_type = $_POST['workout_type']; 

        if (!empty($exercise_name_to_add)) {
            
            if ($current_type == 'strength') {
                $strength_rows++;
                $exercises_post[$strength_rows - 1] = $exercise_name_to_add;
            } elseif ($current_type == 'cardio') {
                $cardio_rows++;
                $cardio_exercise_name_post[$cardio_rows - 1] = $exercise_name_to_add;
            }

            $log_message = "<span class='text-green-500'>$exercise_name_to_add added to $current_type log. Fill in metrics.</span>";
        }
        $workout_selector_value = $current_type;
    }
    elseif (isset($_POST['createExerciseBtn'])) {
        $search_term_for_creation = trim($_POST['search_term'] ?? '');
        
        if(!empty($search_term_for_creation)) {
            createExercise($search_term_for_creation); 
            $log_message = "<span class='text-green-500'>New exercise '$search_term_for_creation' created and ready for log.</span>";
            
            // Clear search state
            $search_results = [];
            $search_term = '';
        } else {
            $log_message = "<span class='text-red-500'>Cannot create empty exercise.</span>";
        }
    }
}


// the statments below handle logging an exercise after its been fully recorded
if (isset($_POST['logWorkoutBtn'])) {

    $workout_date = $_POST['workout_date'] ?? ''; 
    $workout_type = $_POST['workout_type'] ?? '';
    $workout_location = $_POST['locationSelector'] ?? '';

    $current_type = $_POST['workout_type']; 
    $strength_rows = (int) ($_POST['strength_rows'] ?? 0);
    $cardio_rows = (int) ($_POST['cardio_rows'] ?? 0);
    $workout_selector_value = $current_type;
    $location_selector_value = $_POST['locationSelector'] ?? '';
    
    if (empty($workout_date)) {
        $log_message = "<span class='text-red-500'>Please select a date for the workout.</span>";
    } elseif (empty($workout_type)) {
        $log_message = "<span class='text-red-500'>Please select a workout type (Strength or Cardio).</span>";
    } 
    
    elseif ($workout_type == 'strength') {
        $valid_exercises_count = 0;
        $instance_ids = [];
        
        for ($i = 0; $i < count($exercises_post); $i++) {
            $ex_name = trim($exercises_post[$i] ?? '');
            $ex_sets = trim($sets_post[$i] ?? '');
            $ex_reps = trim($reps_post[$i] ?? '');
            $ex_weight = trim($weight_post[$i] ?? '');

            if (!empty($ex_name) && is_numeric($ex_sets) && $ex_sets > 0 && is_numeric($ex_reps) && $ex_reps > 0 && is_numeric($ex_weight) && $ex_weight > 0) {
                $instance_id = createStrengthExerciseInstance($ex_name, $workout_date, $ex_sets, $ex_reps, $ex_weight);
                if ($instance_id > 0) {
                    $instance_ids[] = $instance_id;
                    $valid_exercises_count++;
                } else {
                    $log_message .= "<span class='text-red-500'>Failed to log $ex_name. Check DB connection/permissions.</span>";
                }
            }
        }
        
        logWorkout($workout_date, $workout_location, $instance_ids, $user_id);

        if ($valid_exercises_count > 0) {
            $log_message = "<span class='text-green-500'>Successfully logged $valid_exercises_count strength exercise(s) on $workout_date.</span>";
            $current_type = ''; $strength_rows = 0; $cardio_rows = 0; $workout_selector_value = ''; $location_selector_value = '';
        } else {
            $log_message = "<span class='text-red-500'>Please add at least one valid strength exercise (all fields required).</span>";
        }
    } 
    
    elseif ($workout_type == 'cardio') {
        $valid_metrics_count = 0;
        $instance_ids = [];
        for ($i = 0; $i < count($cardio_exercise_name_post); $i++) {
            $ex_name = trim($cardio_exercise_name_post[$i] ?? '');
            $ex_distance = trim($cardio_distance_post[$i] ?? '');
            $ex_duration = trim($cardio_duration_post[$i] ?? '');

            if (!empty($ex_name) && is_numeric($ex_distance) && $ex_distance > 0 && is_numeric($ex_duration) && $ex_duration > 0) {
                $instance_id = createCardioExerciseInstance($ex_name, $workout_date, $ex_distance, $ex_duration);
                if ($instance_id > 0) {
                    $instance_ids[] = $instance_id;
                    $valid_metrics_count++;
                } else {
                    $log_message .= "<span class='text-red-500'>Failed to log $ex_name. Check DB connection/permissions.</span>";
                }
            }
        }

        logWorkout($workout_date, $workout_location, $instance_ids, $user_id);

        if ($valid_metrics_count > 0) {
            $log_message = "<span class='text-green-500'>Successfully logged $valid_metrics_count cardio exercise(s) on $workout_date.</span>";
            $current_type = ''; $strength_rows = 0; $cardio_rows = 0; $workout_selector_value = ''; $location_selector_value = '';
        } else {
            $log_message = "<span class='text-red-500'>Please add at least one valid cardio exercise (name, distance, and duration are required).</span>";
        }
    }
} 
if ($current_type == 'strength' && $strength_rows == 0) {
    $strength_rows = 0;
}
if ($current_type == 'cardio' && $cardio_rows == 0) {
    $cardio_rows = 0;
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
        /* Style for the action buttons in the search results */
        .action-button {
            padding: 0.25rem 0.5rem;
            border: 1px solid #1a1a1a;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.15s;
            line-height: 1.25;
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
                    <li><a href="logWorkout.php" class="text-black font-extrabold text-xl border-b-2 border-black">Log Workout</a></li>
                    <li><a href="group_workout.php" class="hover:text-black">Group Workout</a></li>
                    <li class="pt-4"><a href="login.php" class="text-red-500 hover:text-red-700">Logout</a></li>
                </ul>
            </nav>

            <main class="lg:w-4/5 flex flex-col xl:flex-row gap-8">
                
                <!-- Log Entry section -->
                <div class="xl:w-3/5 w-full card-bg-dark p-6 rounded-lg shadow-xl text-white">
                    <h2 class="text-2xl font-semibold mb-6 text-center">Log Workout</h2>
                    
                    <?php if (!empty($log_message)): ?>
                        <div class="mb-4 text-center text-sm font-bold">
                            <?php echo $log_message; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="logWorkout.php" class="space-y-6">
                        
                        <input type="hidden" name="workout_type" value="<?php echo htmlspecialchars($current_type); ?>">
                        <input type="hidden" name="strength_rows" value="<?php echo $strength_rows; ?>">
                        <input type="hidden" name="cardio_rows" value="<?php echo $cardio_rows; ?>">
                        
                        <?php 
                        // Strength inputs section
                        for ($i = 0; $i < $strength_rows; $i++): 
                            if ($i < count($exercises_post)):
                            ?>
                                <input type="hidden" name="exercise_name[]" value="<?php echo htmlspecialchars($exercises_post[$i] ?? ''); ?>">
                                <input type="hidden" name="sets[]" value="<?php echo htmlspecialchars($sets_post[$i] ?? ''); ?>">
                                <input type="hidden" name="reps[]" value="<?php echo htmlspecialchars($reps_post[$i] ?? ''); ?>">
                                <input type="hidden" name="weight[]" value="<?php echo htmlspecialchars($weight_post[$i] ?? ''); ?>">
                            <?php 
                            endif; 
                        endfor; 
                        
                        // Cardio inputs section
                        for ($i = 0; $i < $cardio_rows; $i++): 
                            if ($i < count($cardio_exercise_name_post)):
                            ?>
                                <input type="hidden" name="cardio_exercise_name[]" value="<?php echo htmlspecialchars($cardio_exercise_name_post[$i] ?? ''); ?>">
                                <input type="hidden" name="cardio_distance[]" value="<?php echo htmlspecialchars($cardio_distance_post[$i] ?? ''); ?>">
                                <input type="hidden" name="cardio_duration[]" value="<?php echo htmlspecialchars($cardio_duration_post[$i] ?? ''); ?>">
                            <?php
                            endif;
                        endfor;
                        ?>

                        <!-- date -->
                        <div class="relative mb-6 border-b border-gray-500 pb-6">
                            <h3 class="text-xl font-medium mb-3">Workout Date:</h3>
                            <input type="date" name="workout_date" 
                                class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner appearance-none focus:outline-none"
                                value="<?php echo htmlspecialchars($selected_workout_date); ?>" 
                            />
                        </div>

                        <div class="relative mb-6 border-b border-gray-500 pb-6">
                            <h3 class="text-xl font-medium mb-3">Select Location:</h3>
                            <select id="locationSelector" name="locationSelector" 
                                class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner appearance-none pr-8 focus:outline-none"
                                onchange="this.form.submit()"
                            >
                                <option value="" <?php echo $location_selector_value == '' ? 'selected' : ''; ?>>--- Choose Location ---</option>
                                <option value="AFC" <?php echo $location_selector_value == 'AFC' ? 'selected' : ''; ?>>AFC</option>
                                <option value="SRC" <?php echo $location_selector_value == 'SRC' ? 'selected' : ''; ?>>SRC</option>
                                <option value="NGRC" <?php echo $location_selector_value == 'NGRC' ? 'selected' : ''; ?>>NGRC</option>
                                <option value="MEM" <?php echo $location_selector_value == 'MEM' ? 'selected' : ''; ?>>MEM</option>


                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                <svg class="w-4 h-4 text-black fill-current" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                            </div>
                        </div>

                        <!-- Workout Type -->
                        <div class="relative mb-6 border-b border-gray-500 pb-6">
                            <h3 class="text-xl font-medium mb-3">Select Workout Category:</h3>
                            <select id="workoutSelector" name="workoutSelector" 
                                class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner appearance-none pr-8 focus:outline-none"
                                onchange="this.form.submit()"
                            >
                                <option value="" <?php echo $workout_selector_value == '' ? 'selected' : ''; ?>>--- Choose Type ---</option>
                                <option value="strength" <?php echo $workout_selector_value == 'strength' ? 'selected' : ''; ?>>Strength</option>
                                <option value="cardio" <?php echo $workout_selector_value == 'cardio' ? 'selected' : ''; ?>>Cardio</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                <svg class="w-4 h-4 text-black fill-current" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                            </div>
                        </div>


                        <!-- Stength inputs-->
                        <div id="strengthInputs" class="space-y-4 <?php echo $current_type != 'strength' ? 'hidden' : ''; ?> border-t border-gray-500 pt-6">
                            <h3 class="text-xl font-medium">Strength Exercises:</h3>
                            <div id="strengthContainer" class="space-y-4">
                                <?php 
                                for ($i = 0; $i < $strength_rows; $i++): 
                                    $exercise_name = htmlspecialchars($exercises_post[$i] ?? '');
                                    $sets = htmlspecialchars($sets_post[$i] ?? '');
                                    $reps = htmlspecialchars($reps_post[$i] ?? '');
                                    $weight = htmlspecialchars($weight_post[$i] ?? '');
                                ?>
                                <div class="flex space-x-2 items-center">
                                    <input type="text" name="exercise_name[]" placeholder="Exercise Name" 
                                        class="w-full bg-white text-black p-2 rounded-md border border-gray-300 shadow-inner focus:outline-none text-sm"
                                        value="<?php echo $exercise_name; ?>"
                                        readonly
                                    >
                                    <input type="number" name="sets[]" placeholder="Sets" min="1"
                                        class="w-16 bg-white text-black p-2 rounded-md border border-gray-300 shadow-inner focus:outline-none text-sm text-center"
                                        value="<?php echo $sets; ?>"
                                    >
                                    <input type="number" name="reps[]" placeholder="Reps" min="1"
                                        class="w-16 bg-white text-black p-2 rounded-md border border-gray-300 shadow-inner focus:outline-none text-sm text-center"
                                        value="<?php echo $reps; ?>"
                                    >
                                    <input type="number" name="weight[]" placeholder="Weight" min="1"
                                        class="w-16 bg-white text-black p-2 rounded-md border border-gray-300 shadow-inner focus:outline-none text-sm text-center"
                                        value="<?php echo $weight; ?>"
                                    >
                                </div>
                                <?php endfor; ?>
                            </div>
                            
                        </div>
                        
                        <!-- Cardio inputs-->
                        <div id="cardioInputs" class="space-y-4 <?php echo $current_type != 'cardio' ? 'hidden' : ''; ?> border-t border-gray-500 pt-6">
                            <h3 class="text-xl font-medium">Cardio Exercises:</h3>
                            <div id="cardioContainer" class="space-y-4">
                                <?php 
                                for ($i = 0; $i < $cardio_rows; $i++): 
                                    $exercise_name = htmlspecialchars($cardio_exercise_name_post[$i] ?? '');
                                    $distance = htmlspecialchars($cardio_distance_post[$i] ?? '');
                                    $duration = htmlspecialchars($cardio_duration_post[$i] ?? '');
                                ?>
                                <div class="flex space-x-2 items-center">
                                    <input type="text" name="cardio_exercise_name[]" placeholder="Exercise Name" 
                                        class="w-full bg-white text-black p-2 rounded-md border border-gray-300 shadow-inner focus:outline-none text-sm"
                                        value="<?php echo $exercise_name; ?>"
                                        readonly
                                    >
                                    <input type="number" name="cardio_distance[]" placeholder="Distance (mi)" min="0.1" step="0.1"
                                        class="w-1/3 bg-white text-black p-2 rounded-md border border-gray-300 shadow-inner focus:outline-none text-sm text-center"
                                        value="<?php echo $distance; ?>"
                                    >
                                    <input type="number" name="cardio_duration[]" placeholder="Duration (min)" min="1" step="1"
                                        class="w-1/3 bg-white text-black p-2 rounded-md border border-gray-300 shadow-inner focus:outline-none text-sm text-center"
                                        value="<?php echo $duration; ?>"
                                    >
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div id="submitButtons" class="pt-6 space-y-4 <?php echo empty($current_type) ? 'hidden' : ''; ?> border-t border-gray-500">
                            <button 
                                type="submit" 
                                name="logWorkoutBtn"
                                class="log-button w-full bg-white hover:bg-gray-200 text-black border-black"
                            >
                                Log Final Workout
                            </button>
                            <a href="logWorkout.php" 
                                class="log-button w-full bg-red-100 hover:bg-red-200 text-red-800 border-red-500 text-center block no-underline"
                            >
                                Clear All Inputs
                            </a>
                        </div>

                    </form>
                </div>
                
                <div class="xl:w-2/5 w-full card-bg-dark p-6 rounded-lg shadow-xl text-white <?php echo empty($current_type) ? 'hidden' : ''; ?>">
                    <h2 class="text-2xl font-semibold mb-6 text-center">Search Exercises</h2>
                    <form method="POST" action="logWorkout.php" class="space-y-4 border-b border-gray-500 pb-4 mb-4">
                        <?php 
                        echo '<input type="hidden" name="workout_type" value="' . htmlspecialchars($current_type) . '">';
                        echo '<input type="hidden" name="strength_rows" value="' . $strength_rows . '">';
                        echo '<input type="hidden" name="cardio_rows" value="' . $cardio_rows . '">';
                        echo '<input type="hidden" name="workout_date" value="' . htmlspecialchars($selected_workout_date) . '">';
                        echo '<input type="hidden" name="locationSelector" value="' . htmlspecialchars($location_selector_value) . '">';
                        
                        // Pass current log data
                        for ($i = 0; $i < $strength_rows; $i++) {
                            echo '<input type="hidden" name="exercise_name[]" value="' . htmlspecialchars($exercises_post[$i] ?? '') . '">';
                            echo '<input type="hidden" name="sets[]" value="' . htmlspecialchars($_POST['sets'][$i] ?? '') . '">';
                            echo '<input type="hidden" name="reps[]" value="' . htmlspecialchars($_POST['reps'][$i] ?? '') . '">';
                            echo '<input type="hidden" name="weight[]" value="' . htmlspecialchars($_POST['weight'][$i] ?? '') . '">';
                        }
                        
                        for ($i = 0; $i < $cardio_rows; $i++) {
                            echo '<input type="hidden" name="cardio_exercise_name[]" value="' . htmlspecialchars($cardio_exercise_name_post[$i] ?? '') . '">';
                            echo '<input type="hidden" name="cardio_distance[]" value="' . htmlspecialchars($cardio_distance_post[$i] ?? '') . '">';
                            echo '<input type="hidden" name="cardio_duration[]" value="' . htmlspecialchars($cardio_duration_post[$i] ?? '') . '">';
                        }
                        ?>

                        <input 
                            type="text" 
                            name="search_term" 
                            placeholder="Type exercise name..." 
                            required
                            value="<?php echo htmlspecialchars($search_term); ?>"
                            class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner focus:outline-none"
                        >
                        <button 
                            type="submit" 
                            name="searchExerciseBtn"
                            class="log-button w-full bg-blue-500 hover:bg-blue-600 text-white"
                        >
                            Search
                        </button>
                    </form>
                    
                    <!-- Exercise Search Section -->
                    <div class="space-y-3">
                        <?php if (isset($_POST['searchExerciseBtn'])): ?>
                            <h3 class="text-lg font-bold mb-2">Results for "<?php echo htmlspecialchars($search_term); ?>"</h3>
                            
                            <?php if ($search_results && count($search_results) > 0): ?>
                                <?php foreach ($search_results as $result): ?>
                                    <div class="flex justify-between items-center bg-gray-700 p-3 rounded-md">
                                        <span class="font-medium text-lg"><?php echo htmlspecialchars($result['EXERCISE_NAME']); ?></span>
                                        <form method="POST" action="logWorkout.php" class="inline">
                                            <?php 
                                            echo '<input type="hidden" name="workout_type" value="' . htmlspecialchars($current_type) . '">';
                                            echo '<input type="hidden" name="strength_rows" value="' . $strength_rows . '">';
                                            echo '<input type="hidden" name="cardio_rows" value="' . $cardio_rows . '">';
                                            echo '<input type="hidden" name="workout_date" value="' . htmlspecialchars($selected_workout_date) . '">';
                                            echo '<input type="hidden" name="locationSelector" value="' . htmlspecialchars($location_selector_value) . '">';
                                            echo '<input type="hidden" name="exercise_name_to_add" value="' . htmlspecialchars($result['EXERCISE_NAME']) . '">';
                                            
                                            for ($i = 0; $i < $strength_rows; $i++) {
                                                echo '<input type="hidden" name="exercise_name[]" value="' . htmlspecialchars($exercises_post[$i] ?? '') . '">';
                                                echo '<input type="hidden" name="sets[]" value="' . htmlspecialchars($_POST['sets'][$i] ?? '') . '">';
                                                echo '<input type="hidden" name="reps[]" value="' . htmlspecialchars($_POST['reps'][$i] ?? '') . '">';
                                                echo '<input type="hidden" name="weight[]" value="' . htmlspecialchars($_POST['weight'][$i] ?? '') . '">';
                                            }
                                            
                                            for ($i = 0; $i < $cardio_rows; $i++) {
                                                echo '<input type="hidden" name="cardio_exercise_name[]" value="' . htmlspecialchars($cardio_exercise_name_post[$i] ?? '') . '">';
                                                echo '<input type="hidden" name="cardio_distance[]" value="' . htmlspecialchars($cardio_distance_post[$i] ?? '') . '">';
                                                echo '<input type="hidden" name="cardio_duration[]" value="' . htmlspecialchars($cardio_duration_post[$i] ?? '') . '">';
                                            }
                                            ?>
                                            <button 
                                                type="submit" 
                                                name="add_selected_exercise"
                                                class="action-button bg-yellow-300 hover:bg-yellow-400 text-black"
                                            >
                                                Add Exercise
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>

                            <?php else: ?>
                                <div class="p-3 bg-gray-600 rounded-md text-center">
                                    <p class="mb-3">No results found for "<?php echo htmlspecialchars($search_term); ?>".</p>
                                    
                                    <!-- Create New Exercises Section-->
                                    <form method="POST" action="logWorkout.php" class="inline">
                                        <?php 
                                        echo '<input type="hidden" name="search_term" value="' . htmlspecialchars($search_term) . '">';
                                        echo '<input type="hidden" name="workout_type" value="' . htmlspecialchars($current_type) . '">';
                                        echo '<input type="hidden" name="strength_rows" value="' . $strength_rows . '">';
                                        echo '<input type="hidden" name="cardio_rows" value="' . $cardio_rows . '">';
                                        echo '<input type="hidden" name="workout_date" value="' . htmlspecialchars($selected_workout_date) . '">';
                                        echo '<input type="hidden" name="locationSelector" value="' . htmlspecialchars($location_selector_value) . '">';

                                        for ($i = 0; $i < $strength_rows; $i++) {
                                            echo '<input type="hidden" name="exercise_name[]" value="' . htmlspecialchars($exercises_post[$i] ?? '') . '">';
                                            echo '<input type="hidden" name="sets[]" value="' . htmlspecialchars($_POST['sets'][$i] ?? '') . '">';
                                            echo '<input type="hidden" name="reps[]" value="' . htmlspecialchars($_POST['reps'][$i] ?? '') . '">';
                                            echo '<input type="hidden" name="weight[]" value="' . htmlspecialchars($_POST['weight'][$i] ?? '') . '">';
                                        }
                                        
                                        for ($i = 0; $i < $cardio_rows; $i++) {
                                            echo '<input type="hidden" name="cardio_exercise_name[]" value="' . htmlspecialchars($cardio_exercise_name_post[$i] ?? '') . '">';
                                            echo '<input type="hidden" name="cardio_distance[]" value="' . htmlspecialchars($cardio_distance_post[$i] ?? '') . '">';
                                            echo '<input type="hidden" name="cardio_duration[]" value="' . htmlspecialchars($cardio_duration_post[$i] ?? '') . '">';
                                        }
                                        ?>
                                        <button 
                                            type="submit" 
                                            name="createExerciseBtn"
                                            class="action-button w-full bg-red-300 hover:bg-red-400 text-black"
                                        >
                                            Create New Exercise
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>

                        <?php else: ?>
                            <p class="text-center text-gray-400">Search for exercises to add them to your log.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
            </main>
        </div>
    </div>
</body>
</html>