<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SOKO MTANDAO| Home & Login</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-image: url(img/he.png);
      background-position: center;
      margin-top: 0px;
      background-size: cover;
      background-repeat: no-repeat;
      color: #fff;
      line-height: 1.6;
    }

    header {
      background-color: rgba(80, 2, 89, 0.9);
      padding: 10px 10px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
      position: fixed;
      width: 100%;
      top: 0;
      margin-top: -6px;
      z-index: 5;
      backdrop-filter: blur(10px);
    }

    /* Logo at Top-Left Corner */
    header .logo {
      display: flex;
      align-items: center;
    }

    header .logo img {
      width:100px; /* Adjust size as needed */
      height: auto;
      margin-right: 20px; /* Space between logo and text */
    }

    header .logo h1 {
      font-size: 32px;
      font-weight: 700;
      color: #fff;
    }

    header nav ul {
      list-style-type: none;
      display: flex;
    }

    header nav ul li {
      margin-left: 20px;
    }

    header nav ul li a {
      color: #fff;
      text-decoration: none;
      font-size: 18px;
      font-weight: 500;
      transition: color 0.3s ease;
    }

    header nav ul li a:hover {
      color: #30e8ee;
    }

    main {
      padding-top: 100px;
      padding: 40px;
      text-align: center;
      background-color: rgba(0, 0, 0, 0.7);
      border-radius: 10px;
      max-width: 1300px;
      margin: 0 auto;
    }

    h2 {
      color: #3baadd;
      font-size: 36px;
      margin-bottom: 20px;
      font-weight: 700;
    }

    h3 {
      font-size: 28px;
      color: #3baadd;
      margin-top: 30px;
      font-weight: 600;
    }

    /* Welcome Section */
    .welcome {
      margin-bottom: 40px;
    }

    .welcome p {
      font-size: 20px;
      margin: 10px 0;
      color: #e0f7fa;
    }

    /* Updated Button Styles */
    .join-btn, .event-item button, .login-container button {
      background-color: #4B0082; /* Dark Purple */
      color: #fff;
      padding: 12px 30px;
      border-radius: 5px;
      font-size: 18px;
      cursor: pointer;
      text-transform: uppercase;
      border: none;
      transition: background-color 0.3s ease, transform 0.3s ease;
    }

    .join-btn:hover, .event-item button:hover, .login-container button:hover {
      background-color: #6A0DAD; /* Lighter Purple on Hover */
      transform: scale(1.05);
    }

    /* Image Section */
    .image-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
      margin-bottom: 40px;
    }

    .image-container img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
      transition: transform 0.3s ease;
    }

    .image-container img:hover {
      transform: scale(1.05);
    }

    /* Announcements Section */
    .announcements ul {
      list-style-type: none;
      text-align: left;
      margin-top: 20px;
      font-size: 18px;
    }

    .announcements ul li {
      margin: 10px 0;
      padding: 10px;
      background-color: rgba(255, 255, 255, 0.1);
      border-radius: 5px;
    }

    .event-item {
      background-color: rgba(224, 247, 250, 0.1);
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 8px;
      font-size: 18px;
      transition: background-color 0.3s ease;
    }

    .event-item:hover {
      background-color: rgba(224, 247, 250, 0.2);
    }

    .event-item h4 {
      font-size: 22px;
      margin-bottom: 10px;
      color: #3baadd;
    }

    /* About Us Page Styling */
    .about-us {
      background-color: rgba(0, 0, 0, 0.7);
      padding: 40px;
      border-radius: 10px;
      margin: 40px auto;
      max-width: 1000px;
      color: #fff;
    }

    .about-us h2 {
      font-size: 36px;
      margin-bottom: 20px;
    }

    .about-us p {
      font-size: 18px;
      margin-bottom: 20px;
      color: #e0f7fa;
    }

    /* About Us Image Section */
    .about-image-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
    }

    .about-image-container img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
      transition: transform 0.3s ease;
    }

    .about-image-container img:hover {
      transform: scale(1.05);
    }

    /* Login Form Styling */
    .login-container {
      background-color: rgba(255, 255, 255, 0.9);
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      width: 300px;
      margin: 40px auto;
      display: none;
      color: #333;
    }

    .login-container h1 {
      font-size: 24px;
      margin-bottom: 20px;
      color: #3baadd;
    }

    .login-container input {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 16px;
    }

    .login-container button {
      width: 100%;
      padding: 12px;
      background-color: #4B0082; /* Dark Purple */
      color: #fff;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
      transition: background-color 0.3s ease;
    }

    .login-container button:hover {
      background-color: #6A0DAD; /* Lighter Purple on Hover */
    }

    .login-container .signup-link {
      margin-top: 10px;
      font-size: 14px;
    }

    .login-container .signup-link a {
      color: #3baadd;
      text-decoration: none;
    }

    .login-container .signup-link a:hover {
      text-decoration: underline;
    }

    /* Footer Styling */
    footer {
      background-color: rgba(80, 2, 89, 0.9);
      color: white;
      text-align: center;
      padding: 10px 0;
      position: fixed;
      bottom: 0;
      width: 100%;
      backdrop-filter: blur(10px);
    }

    /* Responsive Design */
    @media (max-width: 600px) {
      header .logo h1 {
        font-size: 24px; /* Smaller font for mobile */
      }

      header .logo img {
        width: 100px; /* Smaller logo for mobile */
      }

      .login-container {
        width: 90%;
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="img/he.png" alt="Logo"  width: 200px; >
      <h1>SOKO MTANDAO</h1>
    </div>
    <nav>
      <ul>
        <li><a href="#" onclick="showHomePage()">Home</a></li>
        <li><a href="#" onclick="showAboutUsPage()">About Us</a></li>
        <li><a href="#" onclick="showLoginForm()">Login</a></li>
        <li><a href="file:///C:/xampp/htdocs/log/sign%20up.html">Sign Up</a></li>
          <li><a href="https://www.youtube.com/@ministermocky3430" target="_blank">Online Services</a></li>
      </ul>
    </nav>
  </header>

  <main
    <!-- Home Page Content -->
    <section class="home-page">
      <div class="welcome">
        <h2>Welcome to Our Church Family!</h2>
        <p>We are a community united in love and faith. Join us in worship and fellowship.</p>
        <button class="join-btn">Join Us for Worship</button>
      </div>

      <!-- Image Section -->
      <div class="image-container">
        <img src="img/g.png" alt="Church Gathering">
        <img src="img/f.jpg" alt="Worship Service">
        <img src="img/d.jpg" alt="Church Members">
        <img src="img/s.jpg" alt="Prayer Group">
        <img src="img/a.jpg" alt="Church Building">
        <img src="img/z.jpg" alt="Worship Music">
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
          <p>Time: 09:00 AM</p>
          <p>Location: Mbezi Beach</p>
          <button>Sunday Services</button>
        </div>
        <div class="event-item">
          <h4>Prayer Meeting</h4>
          <p>Time: 6:00 PM</p>
          <p>Location: Mbezi Beach</p>
          <button>Testimonies</button>
        </div>
      </div>
    </section>

    <!-- About Us Page Content -->
    <section class="about-us" id="about-us">
      <h2>About Heart of Worship Ministry</h2>
      <p>We are a group of passionate believers, seeking to spread God's love through worship and service to our community. Our mission is to bring people closer to God, inspire spiritual growth, and create a welcoming environment for all.</p>
      <p>Founded in [Year], Heart of Worship Ministry has grown into a vibrant community of believers who are dedicated to worship, fellowship, and outreach. Join us as we continue to make a difference in the world through the love of Christ.</p>

      <!-- About Us Image Section -->
      <div class="about-image-container">
        <img src="img/about1.jpg" alt="Ministry Activities">
        <img src="img/about2.jpg" alt="Bible Study Group">
      </div>
    </section>



    <!-- Login Form Content -->
    <div class="login-container" id="login-container">
      <h1>Welcome to Heart of Worship</h1>
      <p>Please login to access our community and resources.</p>

      <form action=  "heart worship.html" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" placeholder="Enter your username" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" required>

        <button type="submit" class="login-btn"  a href="heart worship.html" >Login</button>
      </form>

      <div class="signup-link">
        <p>New here? <a href="sign up.html">Sign up to become a member.</a></p>
         <footer>
    <p>&copy; 2025 Heart of Worship Church | All Rights Reserved</p>
  </footer>

      </div>
    </div>
  </main>

 

  <script>
    function showHomePage() {
      document.querySelector('.home-page').style.display = 'block';
      document.querySelector('.about-us').style.display = 'none';
      document.getElementById('login-container').style.display = 'none';
    }

    function showAboutUsPage() {
      document.querySelector('.home-page').style.display = 'none';
      document.querySelector('.about-us').style.display = 'block';
      document.getElementById('login-container').style.display = 'none';
    }

    function showLoginForm() {
      document.querySelector('.home-page').style.display = 'none';
      document.querySelector('.about-us').style.display = 'none';
      document.getElementById('login-container').style.display = 'block';
    }
  </script>
</body>
</html>