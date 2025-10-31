
<?php    // for remote connection
// header('Access-Control-Allow-Origin: http://localhost:4200');
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Authorization, Accept, Client-Security-Token, Accept-Encoding');
// header('Access-Control-Max-Age: 1000');  
// header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT');
?>

<?php 
require('connect-db.php');         // include() 
require('request-db.php');

?>

<?php 
if ($_SERVER['REQUEST_METHOD'] == 'POST')  
{

}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">    
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fitness Dashboard</title>
    <meta name="author" content="Alston Hou, Adnan Murtaza, Ben Chang, Kenny Nguyen">
    <meta name="description" content="This web app is designed to help UVA student's track their workouts! It offers competitive features with friends to keep users motivated.">
    <meta name="keywords" content="UVA, Workout, Fitness">
    <link rel="icon" type="image/png" href="https://www.cs.virginia.edu/~up3f/cs4750/images/db-icon.png" />
    <!-- Provided from https://tailwindcss.com/docs/installation/play-cdn-->
    <!-- The following code is used to make the boxes and visuals-->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>

        .card-bg-dark {
            background-color: #355375; 
        }

        .page-text {
            color: #1a1a1a;
        }

        .progress-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 2px solid #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: #fff;
            background-color: #2a415a; 
        }
    </style>
</head>
<body class="bg-white min-h-screen p-4 sm:p-8 font-sans">
    <div class="max-w-7xl mx-auto">
    <header class="mb-8">
            <h1 class="text-5xl font-extrabold page-text">Good Day, USERNAME</h1>
            <p class="text-lg text-gray-700 mt-1">Track your daily activity</p>
        </header>
        <div class="flex flex-col lg:flex-row gap-8">
            
            <nav class="lg:w-1/5">
                <h2 class="text-xl font-bold page-text mb-4">Main Menu</h2>
                <ul class="space-y-3 text-gray-500 font-medium">
                    <li><a href="#" class="text-black font-extrabold text-xl border-b-2 border-black">Dashboard</a></li>
                    <li><a href="#" class="hover:text-black">Log Workout</a></li>
                    <li><a href="#" class="hover:text-black">Group Workout</a></li>
                    <li class="pt-4"><a href="login_form.html" class="text-red-500 hover:text-red-700">Logout</a></li>
                </ul>
            </nav>

            <main class="lg:w-4/5 flex flex-col xl:flex-row gap-8">
                
                <div class="xl:w-3/5 card-bg-dark p-6 rounded-lg shadow-xl text-white">
                    <h2 class="text-2xl font-semibold mb-6">Progress</h2>
                    
                    <div class="mb-8">
                        <select class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner appearance-none pr-8">
                            <option>Select Workout</option>
                            <option>Running - 5k</option>
                            <option>Strength Training</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <svg class="w-4 h-4 text-black fill-current" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mt-12">
                        <p class="text-xl font-medium">Workout Progress</p>
                        <div class="progress-circle">X%</div>
                    </div>
                </div>

                <div class="xl:w-2/5 card-bg-dark p-6 rounded-lg shadow-xl text-white">
                    <h2 class="text-2xl font-semibold mb-6">Leaderboard</h2>

                    <div class="mb-6">
                        <select class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner appearance-none pr-8">
                            <option>Select Workout</option>
                            <option>Daily Steps</option>
                            <option>Total Calories</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <svg class="w-4 h-4 text-black fill-current" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner font-medium">User 1</div>
                        <div class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner font-medium">User 2</div>
                        <div class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner font-medium">User 3</div>
                        <div class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner font-medium">User 4</div>
                        <div class="w-full bg-white text-black p-3 rounded-md border border-gray-300 shadow-inner font-medium">User 5</div>
                    </div>
                </div>
                
            </main>
        </div>
    </div>
    
</body>
</html>
