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

$group_message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['createGroupBtn'])) {
        $group_name = $_POST['groupName'] ?? '';
        $group_message = "<span class='text-green-500'>Group '$group_name' created (Placeholder).</span>";
    } elseif (isset($_POST['manageMemberBtn'])) {
        $member_id = $_POST['memberId'] ?? '';
        $action = $_POST['memberAction'] ?? '';
        $group_message = "<span class='text-green-500'>Member $member_id successfully $action (Placeholder).</span>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Group Workout</title>
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
        /* Custom font color for the overall page structure */
        .page-text {
            color: #1a1a1a;
        }
        /* Style for the button appearance (mimicking the dashboard style) */
        .page-button {
            padding: 0.75rem 1rem;
            border: 2px solid #fff;
            color: #1a1a1a;
            font-weight: 600;
            background-color: #f7f7f7;
            border-radius: 0.375rem; /* rounded-md */
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.15s;
        }
        .page-button:hover {
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
                    <li><a href="log_workout.php" class="hover:text-black">Log Workout</a></li>
                    <li><a href="group_workout.php" class="text-black font-extrabold text-xl border-b-2 border-black">Group Workout</a></li>
                    <li class="pt-4"><a href="login.php" class="text-red-500 hover:text-red-700">Logout</a></li>
                </ul>
            </nav>

            <main class="lg:w-4/5 flex flex-col xl:flex-row gap-8">
                <div class="xl:w-3/5 card-bg-dark p-6 rounded-lg shadow-xl text-white">
                    <h2 class="text-2xl font-semibold mb-6 text-center">Group Management</h2>
                    
                    <?php if (!empty($group_message)): ?>
                        <div class="mb-4 text-center text-sm font-bold">
                            <?php echo $group_message; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="group_workout.php" class="border-b border-gray-400 pb-6 mb-6">
                        <h3 class="text-xl font-medium mb-3">Create New Group</h3>
                        <input 
                            type="text" 
                            name="groupName" 
                            placeholder="Enter Group Name" 
                            required
                            class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner mb-3 focus:outline-none"
                        >
                        <button 
                            type="submit" 
                            name="createGroupBtn"
                            class="page-button w-full bg-blue-100 hover:bg-blue-200 text-blue-800 border-blue-500"
                        >
                            Create Group
                        </button>
                    </form>

                    <form method="POST" action="group_workout.php" class="space-y-4">
                        <h3 class="text-xl font-medium mb-3">Manage Group Members</h3>
                        <select name="currentGroup" class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner appearance-none pr-8 focus:outline-none">
                            <option value="">Select Your Group</option>
                            <option value="GroupA">The Wahoos</option>
                            <option value="GroupB">Fitness Fanatics</option>
                        </select>
                        <input 
                            type="text" 
                            name="memberId" 
                            placeholder="Member Computing ID" 
                            required
                            class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner focus:outline-none"
                        >
                        
                        <div class="flex space-x-4">
                            <button 
                                type="submit" 
                                name="manageMemberBtn"
                                value="add"
                                class="page-button w-1/2 bg-green-100 hover:bg-green-200 text-green-800 border-green-500"
                            >
                                Add Member
                            </button>
                            <button 
                                type="submit" 
                                name="manageMemberBtn"
                                value="remove"
                                class="page-button w-1/2 bg-red-100 hover:bg-red-200 text-red-800 border-red-500"
                            >
                                Remove Member
                            </button>
                        </div>
                    </form>
                </div>

                <div class="xl:w-2/5 card-bg-dark p-6 rounded-lg shadow-xl text-white">
                    <h2 class="text-2xl font-semibold mb-6">Group Leaderboard</h2>

                    <div class="mb-6">
                        <select class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner appearance-none pr-8">
                            <option>Select Group</option>
                            <option>The Wahoos</option>
                            <option>Fitness Fanatics</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <svg class="w-4 h-4 text-black fill-current" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner font-medium">1. Alice (150 pts)</div>
                        <div class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner font-medium">2. Bob (140 pts)</div>
                        <div class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner font-medium">3. You (120 pts)</div>
                        <div class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner font-medium">4. Charlie (110 pts)</div>
                        <div class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner font-medium">5. Dawn (90 pts)</div>
                    </div>
                </div>
                
            </main>
        </div>
    </div>
</body>
</html>