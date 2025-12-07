<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require('connect-db.php');
require('request-db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$nameRow = getNameByID($user_id);
$name = $nameRow ? htmlspecialchars($nameRow['user_name']) : "Guest";

$friend_message = "";
$workout_message = "";
$workout_data = [];

# Handle Add Friend 
if (isset($_POST['addFriendBtn'])) {
    $friendCompId = trim($_POST['friend_compid']);
    if (!empty($friendCompId)) {
        $result = addFriend($user_id, $friendCompId);
        $friend_message = $result ? "Friend added!" : "User not found or already friends.";
    }
}

# Handle Workout Lookup by Date 
$selected_date = $_POST['selected_date'] ?? date('Y-m-d');
if (isset($_POST['lookupWorkoutBtn'])) {
    $workout_data = getWorkoutByDate($user_id, $selected_date);
    if (!$workout_data) {
        $workout_message = "No workout logged on this date.";
    }
}

# Leaderboard Data 
$leaderboard = getLeaderboard($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">    
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Fitness Dashboard</title>
</head>

<body class="bg-white min-h-screen p-6 font-sans">
<div class="max-w-7xl mx-auto">
    
    <!-- Header -->
    <header class="mb-8">
        <h1 class="text-5xl font-extrabold">Good Day, <?php echo $name; ?></h1>
        <p class="text-lg text-gray-700">Track your daily activity</p>
    </header>

    <div class="flex flex-col lg:flex-row gap-8">

        <!-- Sidebar -->
        <nav class="lg:w-1/5">
            <h2 class="text-xl font-bold mb-4">Main Menu</h2>
            <ul class="space-y-3 text-gray-500 font-medium">
                <li><a href="dashboard.php" class="text-black font-bold text-xl border-b-2 border-black">Dashboard</a></li>
                <li><a href="logWorkout.php" class="hover:text-black">Log Workout</a></li>
                <li><a href="group.php" class="hover:text-black">Group Workout</a></li>
                <li class="pt-4"><a href="login.php" class="text-red-500 hover:text-red-700">Logout</a></li>
            </ul>
        </nav>

        <!-- MAIN CONTENT AREA -->
        <main class="lg:w-4/5 flex flex-col xl:flex-row gap-8">

            <!-- TRACK WORKOUT -->
            <div class="xl:w-3/5 p-6 rounded-lg shadow-xl bg-[#355375] text-white">
                <h2 class="text-2xl font-semibold mb-6">Track Your Workout</h2>

                <form method="POST" class="mb-6 space-y-4">
                    <label class="block text-lg">Select a Date</label>
                    <input type="date" name="selected_date" value="<?php echo $selected_date; ?>"
                           class="w-full bg-white text-black p-3 rounded-md shadow-inner">
                    <button type="submit" name="lookupWorkoutBtn"
                            class="mt-2 bg-blue-300 hover:bg-blue-400 text-black px-4 py-2 rounded-md">
                        Look Up Workout
                    </button>
                </form>

                <?php if ($workout_message): ?>
                    <p class="text-red-300 font-semibold"><?php echo $workout_message; ?></p>
                <?php endif; ?>

                <?php if ($workout_data): ?>
                    <div class="bg-white text-black p-4 rounded-md mt-4">
                        <h3 class="text-xl font-bold mb-2">Workout for <?php echo $selected_date; ?></h3>

                        <?php foreach ($workout_data as $w): ?>
                            <div class="border-b py-2">
                                <p><strong><?php echo htmlspecialchars($w['exercise_name']); ?></strong></p>
                                <p>Sets: <?php echo $w['SETS']; ?> | Reps: <?php echo $w['REPS']; ?></p>
                                <?php if (isset($w['duration'])): ?>
                                    <p>Duration: <?php echo $w['duration']; ?> min</p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- RIGHT SIDE COLUMN -->
            <div class="xl:w-2/5 flex flex-col gap-8">

                <!-- ADD FRIEND -->
                <div class="p-6 rounded-lg shadow-xl bg-[#355375] text-white">
                    <h2 class="text-2xl font-semibold mb-4">Add a Friend</h2>

                    <form method="POST" class="space-y-4">
                        <input type="text" name="friend_compid" placeholder="Enter friend's computing ID"
                               class="w-full bg-white text-black p-3 rounded-md shadow-inner">
                        <button type="submit" name="addFriendBtn"
                                class="bg-green-300 hover:bg-green-400 text-black px-4 py-2 rounded-md">
                            Add Friend
                        </button>
                    </form>

                    <?php if ($friend_message): ?>
                        <p class="mt-3 text-yellow-200 font-semibold"><?php echo $friend_message; ?></p>
                    <?php endif; ?>
                </div>

                <!-- LEADERBOARD -->
                <div class="p-6 rounded-lg shadow-xl bg-[#355375] text-white">
                    <h2 class="text-2xl font-semibold mb-4">Leaderboard</h2>

                    <?php if ($leaderboard): ?>
                        <?php foreach ($leaderboard as $row): ?>
                            <div class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner mb-2">
                                <strong><?php echo htmlspecialchars($row['NAME']); ?></strong>
                                <span class="float-right font-bold"><?php echo $row['SCORE']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-200">No friends or leaderboard data yet.</p>
                    <?php endif; ?>

                </div>

            </div>

        </main>
    </div>
</div>
</body>
</html>
