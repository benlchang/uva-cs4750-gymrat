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

$user_id = $_SESSION['user_id'];
$group_message = '';
$group_data = []; // Holds members/stats if a group is selected
$selected_group_id = $_POST['selectedGroup'] ?? '';
$groups_list = getUserGroups($user_id); 
$selected_group_name = getGroupNameFromID($selected_group_id);

// State control: 'SELECT' (default), 'CREATE' (to show creation form), 'MANAGE' (after group is selected/created)
$page_state = $_POST['page_state'] ?? 'SELECT'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (isset($_POST['showCreateFormBtn'])) {
        $page_state = 'CREATE';
    }
    
    elseif (isset($_POST['createGroupBtn'])) {
        $group_name = trim($_POST['groupName'] ?? '');
        if (!empty($group_name)) {
            $selected_group_id = createNewGroup($group_name, $user_id); 
            $group_message = "<span class='text-green-500'>Group '$group_name' created successfully.</span>";
            $page_state = 'MANAGE'; // Switch to manage the newly created group
        } else {
            $group_message = "<span class='text-red-500'>Group name cannot be empty.</span>";
            $page_state = 'CREATE'; // Stay on creation form
        }
    }
    
    elseif (isset($_POST['selectedGroup']) && $_POST['selectedGroup'] !== '') {
        $selected_group_id = $_POST['selectedGroup'];
        
        $page_state = 'MANAGE';
        $members = getUsersInGroup($selected_group_id);

        $leaderboard = [];

        foreach ($members as $m) {
            $leaderboard[] = [
                'name' => $m['F_NAME'] . ' ' . $m['L_NAME'],
                'score' => getNumWorkoutsFromID($m['USER_ID'])
            ];
        }

        usort($leaderboard, function($a, $b) {
            return $b['score'] - $a['score'];
        });

        $group_data = $leaderboard;        
    }
    
    elseif (isset($_POST['manageMemberBtn'])) {

        $member_id = $_POST['memberId'] ?? '';
        $selected_group_id = $_POST['groupIdHidden'] ?? '';
        $action = $_POST['manageMemberBtn'];  // "add_member" or "remove_member"

        if ($action === "add_member") {
            $result = addGroupMember($selected_group_id, $member_id);
        } 
        elseif ($action === "remove_member") {
            $result = removeGroupMember($selected_group_id, $member_id);
        }

        $group_message = "<span class='text-green-500'>$result</span>";

        $members = getUsersInGroup($selected_group_id);

        $leaderboard = [];

        foreach ($members as $m) {
            $leaderboard[] = [
                'name' => $m['F_NAME'] . ' ' . $m['L_NAME'],
                'score' => getNumWorkoutsFromID($m['USER_ID'])
            ];
        }

        usort($leaderboard, function($a, $b) {
            return $b['score'] - $a['score'];
        });

        $group_data = $leaderboard;

        $page_state = 'MANAGE';
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
        .select-list-item {
            background-color: white; 
            color: black; 
            padding: 12px; 
            border-radius: 6px; 
            font-weight: 600;
            cursor: pointer;
            border: 1px solid #1a1a1a;
        }
        .select-list-item:hover {
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
                    <li><a href="logWorkout.php" class="hover:text-black">Log Workout</a></li>
                    <li><a href="group.php" class="text-black font-extrabold text-xl border-b-2 border-black">Group Workout</a></li>
                    <li class="pt-4"><a href="login.php" class="text-red-500 hover:text-red-700">Logout</a></li>
                </ul>
            </nav>

            <main class="lg:w-4/5 flex flex-col xl:flex-row gap-8">
                
                <!-- MAIN GROUP CARD (Dynamic Content Based on Page State) -->
                <div class="w-full card-bg-dark p-6 rounded-lg shadow-xl text-white">
                    <h2 class="text-3xl font-bold mb-6 text-center">Group Management</h2>
                    
                    <?php if (!empty($group_message)): ?>
                        <div class="mb-4 text-center text-sm font-bold">
                            <?php echo $group_message; ?>
                        </div>
                    <?php endif; ?>

                    <!-- SELECT GROUP -->
                    <?php if ($page_state == 'SELECT'): ?>
                        <h3 class="text-xl font-medium mb-4">Choose an Action:</h3>
                        
                        <form method="POST" action="group.php" class="space-y-4">
                            <input type="hidden" name="page_state" value="SELECT">
                            
                            <?php if (!empty($groups_list)): ?>
                                <h4 class="text-lg font-semibold mb-2">My Groups (Select to Manage):</h4>
                                <select name="selectedGroup" 
                                    class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner appearance-none pr-8 focus:outline-none"
                                >
                                    <option value="">--- Select Group ---</option>
                                    <?php foreach ($groups_list as $group): ?>
                                        <option value="<?php echo htmlspecialchars($group['id']); ?>">
                                            <?php echo htmlspecialchars($group['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button 
                                    type="submit" 
                                    class="page-button w-full bg-yellow-300 hover:bg-yellow-400 text-black mt-2"
                                >
                                    View Group Stats
                                </button>
                            <?php else: ?>
                                <p class="text-center py-4 text-gray-300">You are not currently in any groups.</p>
                            <?php endif; ?>
                        </form>

                        <div class="border-t border-gray-500 pt-6 mt-6">
                            <form method="POST" action="group.php">
                                <input type="hidden" name="showCreateFormBtn" value="1">
                                <button 
                                    type="submit" 
                                    class="page-button w-full bg-green-500 hover:bg-green-600 text-white"
                                >
                                    + Create a New Group
                                </button>
                            </form>
                        </div>

                    <!-- CREATE NEW GROUP FORM -->
                    <?php elseif ($page_state == 'CREATE'): ?>
                        <h3 class="text-xl font-medium mb-4">Create Group:</h3>
                        
                        <form method="POST" action="group.php" class="space-y-4">
                            <input type="hidden" name="page_state" value="CREATE">
                            <input 
                                type="text" 
                                name="groupName" 
                                placeholder="Enter Group Name (e.g., The Wahoos)" 
                                required
                                class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner focus:outline-none"
                            >
                            <button 
                                type="submit" 
                                name="createGroupBtn"
                                class="page-button w-full bg-green-500 hover:bg-green-600 text-white"
                            >
                                Submit & Start Managing
                            </button>
                        </form>
                        <div class="mt-4 text-center">
                            <form method="POST" action="group.php" class="inline">
                                <input type="hidden" name="page_state" value="SELECT">
                                <button type="submit" class="text-sm text-gray-300 hover:underline">Back to Group Select</button>
                            </form>
                        </div>

                    <!-- MANAGE / VIEW DETAILS -->
                    <?php elseif ($page_state == 'MANAGE'): ?>
                        <div class="mb-6 border-b border-gray-500 pb-4">
                            <h3 class="text-2xl font-bold">Group Details (Name: <?php echo htmlspecialchars($selected_group_name); ?>)</h3>
                        </div>
                        
                        <!-- Member Management Form -->
                        <h3 class="text-xl font-medium mb-3">Add/Remove Members</h3>
                        <form method="POST" action="group.php" class="space-y-4 border-b border-gray-500 pb-6 mb-6">
                            <input type="hidden" name="page_state" value="MANAGE">
                            <input type="hidden" name="groupIdHidden" value="<?php echo htmlspecialchars($selected_group_id); ?>">
                            
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
                                    value="add_member"
                                    class="page-button w-1/2 bg-green-300 hover:bg-green-400 text-black"
                                >
                                    Add Member
                                </button>
                                <button 
                                    type="submit" 
                                    name="manageMemberBtn"
                                    value="remove_member"
                                    class="page-button w-1/2 bg-red-300 hover:bg-red-400 text-black"
                                >
                                    Remove Member
                                </button>
                            </div>
                        </form>
                        
                        <form method="POST" action="group.php">
                            <input type="hidden" name="page_state" value="SELECT">
                            <button type="submit" class="text-sm page-button bg-gray-500 hover:bg-gray-600 text-white w-full">
                                ← Back to Group Selection
                            </button>
                        </form>
                        
                    <?php endif; ?>
                    
                </div>
                
                <!-- Group Leaderboard -->
                <div class="xl:w-2/5 card-bg-dark p-6 rounded-lg shadow-xl text-white <?php echo ($page_state != 'MANAGE') ? 'hidden' : ''; ?>">
                    <h2 class="text-2xl font-semibold mb-6">Workouts Completed</h2>

                    <div class="mb-6">
                        <select class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner appearance-none pr-8">
                            <option><?php echo ($page_state == 'MANAGE') ? 'Group: ' . htmlspecialchars($selected_group_name) : '---'; ?></option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <svg class="w-4 h-4 text-black fill-current" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <?php if (!empty($group_data)): ?>
                            <?php 
                            usort($group_data, fn($a, $b) => $b['score'] <=> $a['score']);
                            $rank = 1;
                            foreach ($group_data as $member): 
                            ?>
                                <div class="w-full bg-white text-black text-sm p-3 rounded-md border border-gray-300 shadow-inner font-medium flex justify-between">
                                    <span><?php echo $rank++; ?>. <?php echo htmlspecialchars($member['name']); ?></span>
                                    <span><?php echo htmlspecialchars($member['score']); ?> workout</span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                             <div class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner font-medium text-center">
                                Select a group to see the leaderboard.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
            </main>
        </div>
    </div>
</body>
</html>