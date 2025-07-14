<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HEART OF WORSHIP MINISTRY Home & Login</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      background-color: whitesmoke;
    }

    header {
      background-color:darksalmon;
      color: rgb(246, 250, 248);
      padding: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    header .logo h1 {
      font-size: 32px;
    }

    header nav ul {
      list-style-type: none;
      display: flex;
    }

    header nav ul li {
      margin-left: 20px;
    }

    header nav ul li a {
      color: rgb(235, 238, 241);
      text-decoration: none;
    }

    header nav ul li a:hover {
      text-decoration: underline;
    }

    main {
      padding: 40px;
      text-align: center;
    }

    h, h2, h3 {
      color: #3baadd;
    }

    /* Login Page Styling */
    .login-container {
      background-color:whitesmoke;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      width: 300px;
      margin: 40px auto;
      display: none; /* Hide login by default */
    }

    .login-container h1 {
      font-size: 24px;
      margin-bottom: 20px;
    }

    .login-container input {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .login-container button {
      width: 100%;
      padding: 12px;
      background-color: #30e8ee;
      color: #f7f5f6;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .login-container button:hover {
      background-color: #000;
    }

    .login-container .signup-link {
      margin-top: 10px;
    }

    .login-container .signup-link a {
      color: darkgrey;
      text-decoration: none;
    }

    .login-container .signup-link a:hover {
      text-decoration: underline;
    }

    /* Home Page Styling */
    .welcome, .announcements, .events {
      margin-bottom: 40px;
    }

    button {
      background-color: darksalmon;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      margin-top: 10px;
    }

    button:hover {
      background-color: darksalmon;
    }

    .announcements ul {
      list-style-type: none;
      text-align: left;
      margin-top: 20px;
    }

    .announcements ul li {
      margin: 10px 0;
    }

    .event-item {
      background-color: #e0f7fa;
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 8px;
    }

    footer {
      background-color:darksalmon;
      color: white;
      text-align: center;
      padding: 10px 0;
      position: fixed;
      bottom: 0;
      width: 100%;
    }
  </style>
</head>
<body>

  <header>
    <div class="">
      <h1>HEART OF WORSHIP MINISTRY-</h1>
    </div>
    <nav>
      <ul>
        <li><a href="#" onclick="showHomePage()">Home</a></li>
        <li><a href="#" onclick="showHomePage()">about us</a></li>
        <li><a href="#" onclick="showLoginForm()">Login</a></li>
      </ul>
    </nav>
  </header>

  <main>

    <!-- Home Page Content -->
    <section class="home-page">
      <div class="welcome">
        <h2>Welcome to Our Church Family!</h2>
        <p>We are a community united in love and faith. Join us in worship and fellowship.</p>
        <button class="join-btn" style="">Join Us for Worship</button>
      </div>

      <div class="announcements">
        <h3>Latest Announcements:</h3>
        <ul>
          <li>Join us this Sunday for our special service at 10 AM.</li>
          <li>Our youth group will meet on Wednesday at 6 PM.</li>
          <li>Women's Bible Study starting next Monday at 7 PM.</li>
        </ul>
      </div>

      <div class="events">
        <h3>Upcoming Events:</h3>
        <div class="event-item">
          <h4>Sunday Worship Service</h4>
          <p>Time: 10:00 AM</p>
    
        <p>Location: mbezi beach  </p>
          <button>sunday services </button>
        </div>
        <div class="event-item">
          <h4>Prayer Meeting</h4>
          <p>Time: 6:00 PM</p>
          <p>Location: mbezi beach</p>
          <button>online services</button>
        </div>
      </div>
    </section>

    <!-- Login Form Content -->
    <div class="login-container" id="login-container">
      <h1>Welcome to Heart of Worship</h1>
      <p>Please login to access our community and resources.</p>

      <form action="/submit-login" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" placeholder="Enter your username" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" required>

        <button type="submit" class="login-btn">Login</button>
      </form>

      <div class="signup-link">
        <p>New here? <a href="#" onclick="showlogim()">login</a></li>>Sign up</a> to become a member.</p>
      </div>
    </div>

  </main>

  <footer>
    <p>&copy; 2025 Heart of Worship Church | All Rights Reserved</p>
  </footer>

  <script>
    function showHomePage() {
      document.querySelector('.home-page').style.display = 'block';
      document.getElementById('login-container').style.display = 'none';
    }

    function showLoginForm() {
      document.querySelector('.home-page').style.display = 'none';
      document.getElementById('login-container').style.display = 'block';
    }
  </script>

</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HEART OF WORSHIP MINISTRY Home & Login</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      background-color: whitesmoke;
    }

    header {
      background-color:darksalmon;
      color: rgb(246, 250, 248);
      padding: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    header .logo h1 {
      font-size: 32px;
    }

    header nav ul {
      list-style-type: none;
      display: flex;
    }

    header nav ul li {
      margin-left: 20px;
    }

    header nav ul li a {
      color: rgb(235, 238, 241);
      text-decoration: none;
    }

    header nav ul li a:hover {
      text-decoration: underline;
    }

    main {
      padding: 40px;
      text-align: center;
    }

    h, h2, h3 {
      color: #3baadd;
    }

    /* Login Page Styling */
    .login-container {
      background-color:whitesmoke;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      width: 300px;
      margin: 40px auto;
      display: none; /* Hide login by default */
    }

    .login-container h1 {
      font-size: 24px;
      margin-bottom: 20px;
    }

    .login-container input {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .login-container button {
      width: 100%;
      padding: 12px;
      background-color: #30e8ee;
      color: #f7f5f6;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .login-container button:hover {
      background-color: #000;
    }

    .login-container .signup-link {
      margin-top: 10px;
    }

    .login-container .signup-link a {
      color: darkgrey;
      text-decoration: none;
    }

    .login-container .signup-link a:hover {
      text-decoration: underline;
    }

    /* Home Page Styling */
    .welcome, .announcements, .events {
      margin-bottom: 40px;
    }

    button {
      background-color: darksalmon;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      margin-top: 10px;
    }

    button:hover {
      background-color: darksalmon;
    }

    .announcements ul {
      list-style-type: none;
      text-align: left;
      margin-top: 20px;
    }

    .announcements ul li {
      margin: 10px 0;
    }

    .event-item {
      background-color: #e0f7fa;
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 8px;
    }

    footer {
      background-color:darksalmon;
      color: white;
      text-align: center;
      padding: 10px 0;
      position: fixed;
      bottom: 0;
      width: 100%;
    }
  </style>
</head>
<body>

  <header>
    <div class="">
      <h1>HEART OF WORSHIP MINISTRY-</h1>
    </div>
    <nav>
      <ul>
        <li><a href="#" onclick="showHomePage()">Home</a></li>
        <li><a href="#" onclick="showHomePage()">about us</a></li>
        <li><a href="#" onclick="showLoginForm()">Login</a></li>
      </ul>
    </nav>
  </header>

  <main>

    <!-- Home Page Content -->
    <section class="home-page">
      <div class="welcome">
        <h2>Welcome to Our Church Family!</h2>
        <p>We are a community united in love and faith. Join us in worship and fellowship.</p>
        <button class="join-btn" style="">Join Us for Worship</button>
      </div>

      <div class="announcements">
        <h3>Latest Announcements:</h3>
        <ul>
          <li>Join us this Sunday for our special service at 10 AM.</li>
          <li>Our youth group will meet on Wednesday at 6 PM.</li>
          <li>Women's Bible Study starting next Monday at 7 PM.</li>
        </ul>
      </div>

      <div class="events">
        <h3>Upcoming Events:</h3>
        <div class="event-item">
          <h4>Sunday Worship Service</h4>
          <p>Time: 10:00 AM</p>
    
        <p>Location: mbezi beach  </p>
          <button>sunday services </button>
        </div>
        <div class="event-item">
          <h4>Prayer Meeting</h4>
          <p>Time: 6:00 PM</p>
          <p>Location: mbezi beach</p>
          <button>online services</button>
        </div>
      </div>
    </section>

    <!-- Login Form Content -->
    <div class="login-container" id="login-container">
      <h1>Welcome to Heart of Worship</h1>
      <p>Please login to access our community and resources.</p>

      <form action="/submit-login" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" placeholder="Enter your username" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" required>

        <button type="submit" class="login-btn">Login</button>
      </form>

      <div class="signup-link">
        <p>New here? <a href="#" onclick="showlogim()">login</a></li>>Sign up</a> to become a member.</p>
      </div>
    </div>

  </main>

  <footer>
    <p>&copy; 2025 Heart of Worship Church | All Rights Reserved</p>
  </footer>

  <script>
    function showHomePage() {
      document.querySelector('.home-page').style.display = 'block';
      document.getElementById('login-container').style.display = 'none';
    }

    function showLoginForm() {
      document.querySelector('.home-page').style.display = 'none';
      document.getElementById('login-container').style.display = 'block';
    }
  </script>

</body>
</html>
