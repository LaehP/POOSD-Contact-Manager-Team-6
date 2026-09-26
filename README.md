# POOSD Contact Manager Team 6

<strong>Our Personal Contact Manager web application</strong> allows users to log in with a username and password, or create an account if they don't have one. Once logged in, they can view, search for, add, or delete contacts, which then takes them to a new screen. On this new screen, users can add contacts on the top of the page, and once saved, they can search for these contacts using the search bar, and the results matching the search will be displayed below the search bar. The users' data is stored, so even if they log out and log back in, the contacts they added stay saved and accessible.

Technologies Used: 
The LAMP stack was used to complete this project. Linux was the Operating System used, the Apache HTTP Web Server was the system receiving network requests from the user's browser, MySQL was used as the database and storage for this project, and stored user credentials, saved contacts, and application data. Finally, PHP was used to implement the backend API endpoints, receiving requests from the JavaScript frontend and communicating with the MySQL database to send or store information from the web app.

Setup Instructions: 
First, you must obtain a Linux-based server with the LAMP stack installed. This project was built using a DigitalOcean Droplet running Ubuntu with Apache, MySQL, and PHP. Then, clone this repository onto the server. Place the index.html as well as the backend and src folder in the server's web root, /var/www/html. Check to make sure the folder structure on droplet is the same as the respository. Create your MySQL database using the SmallProject.sql file. Then configure the PHP database connection with the credentials for the MySQL user on the server. Database passwords and other credentials should not be committed to this repository. Lastly, configure a domain name to point to the server's IP address.

Run and Access the Application: 
After the server, database, and PHP API have been configured, the app can be accessed through the domain name connected to the server. When you open the domain in a web browser, the application should display the login page. From there, test the app's functionality by: logging in with valid credentials from the MySQL database, adding contacts, searching for a contact and deleting a contact. Exit the website and log back in to verify the saved contacts are still connected to the user. The frontend communicates with the backend through the PHP API endpoints.

Assumptions and Limitations: 
A Linux-based server running Apache, PHP, and MySQL is required. You must also have access to a private server droplet or something similar, and you must be able to purchase a custom domain name to host this project. Limitations of this project currently include: there is log out button so you'll exit page to log in again. Also the emails don't have any verification set up so no password recovery can occur if the user forgets their password. 

AI Assistance Disclosure:
This project was developed with assistance from generative AI tools:
- **Tool**: 
- **Dates**: 
- **Scope**:
- **Use**: 
All AI-generated code was reviewed, tested, and modified to meet
assignment requirements. Final implementation reflects my understanding
of the concepts.
