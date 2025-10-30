
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
    <meta name="author" content="Alston Hou, Adnan Murtaza, Ben Chang, Kenny Nguyen">
    <meta name="description" content="This web app is designed to help UVA student's track their workouts! It offers competitive features with friends to keep users motivated.">
    <meta name="keywords" content="UVA, Workout, Fitness">
    <link rel="icon" type="image/png" href="https://www.cs.virginia.edu/~up3f/cs4750/images/db-icon.png" />
    <!-- Provided from https://tailwindcss.com/docs/installation/play-cdn-->
    <!-- The following code is used to make the boxes and visuals-->
    <script src="https://cdn.tailwindcss.com"></script> 
    <style>
        .custom-blue {
            background-color: #87ceeb; 
        }
        

        .raised-element {

            box-shadow: 
                0 4px 6px -1px rgba(0, 0, 0, 0.4), 
                0 -2px 0 0 rgba(255, 255, 255, 0.5) inset; 
            border: 2px solid #000;
        }


        .button-hover:hover {
            box-shadow: 
                0 1px 3px 0 rgba(0, 0, 0, 0.2),
                0 -1px 0 0 rgba(255, 255, 255, 0.3) inset; 
            transform: translateY(1px); 
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-100 font-sans">
    <form method="post" action="<?php $_SERVER['PHP_SELF'] ?>" onsubmit="return validateInput()">
        <div id="loginView" class="w-full max-w-xs md:max-w-sm p-6 bg-blue-300 border-2 border-black rounded-lg shadow-2xl">
            <h1 class="text-white text-center text-3xl font-bold mb-8 pt-4">Login</h1>
            <form id="loginForm" class="space-y-6" onsubmit="handleFormSubmission(event)">
                <div class="raised-element bg-blue-400 p-3 rounded-md">
                    <label for="computingId" class="sr-only">Computing ID</label>
                        <input type="text" 
                            id="computingId" 
                            placeholder="Computing ID" 
                            required 
                            class="w-full bg-transparent text-center text-lg placeholder-white focus:outline-none text-white font-medium"
                        >
                </div>
                <div class="raised-element bg-blue-400 p-3 rounded-md">
                    <label for="password" class="sr-only">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        placeholder="Password" 
                        required 
                        class="w-full bg-transparent text-center text-lg placeholder-white focus:outline-none text-white font-medium"
                    >
                </div>
                <form action="request.php" method="post">
                    <input type="submit" value="Create Account"
                        name="createAccountBtn" class="button-hover w-full raised-element bg-blue-500 text-white font-bold py-3 px-4 rounded-md text-xl tracking-wider transition-all duration-150"
                        title="Click to create your account"
                    />
                </form>
            </form>
            <div class="text-center mt-8 mb-4">
                <span class="text-white text-sm">Or Create Account Using</span>
            </div>
            <button 
                onclick="handleSignUp()"
                class="button-hover w-full raised-element bg-blue-500 text-white font-bold py-3 px-4 rounded-md text-xl tracking-wider transition-all duration-150"
            >
            Create Account
            </button>
            <div id="messageBox" class="mt-6 p-3 bg-green-100 border border-green-400 text-green-700 rounded hidden" role="alert">
                <p id="messageText"></p>
            </div>
        </div>
    </form>
</body>
</html>
