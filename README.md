# YouTube Stats
Webapp that allows users to login with their YouTube/Google account and join a live video stream

[Live](http://www.rfapps.co/youtube_stats)

## Getting started
To test the app, go to the [live page](http://www.rfapps.co/youtube_stats) using the latest version of Google Chrome, or any ES6 compatible browser. You can check if there is any broadcast available by clicking in "Broadcasts" in the navigation bar. If there is none, you can click on "Sign in with Google" and select the "Admin" option, then you will be able to crate new broadcasts and share your existing ones with other users. If there are already broadcasts available and you just want to join them, login as a "User".

After joining a Broadcast, the live video and chat should be available on the page. You can use the form bellow the video to send new messages. The messages should refresh automatically.

You can search for all messages sent by a given user by clicking the "User messages" link in the navbar. Start typing a user name and select one of the options from the auto-complete. All messages sent by the user from within the app will be returned, not only the ones for the selected broadcast. You can type my name "Rodrigo Fontes" for a long list of random messages.

With a broadcast selected, you can view the report for the chat hype by clicking the "chat stats" link in the navbar. A modal will open showing the current number of messages per second (calculated using the number of messages sent in the last hour) and a chart showing the evolution of this stat since the first message was sent. Only messages sent using the app are counted.

## The code
### Back-end
The Google API is abstracted by two files:

* [`config/google_project.php`](./config/google_project.php) is responsible for user authentication
* [`models/broadcast.php`](./models/broadcast.php) is responsible for communications with YouTube

As such, this files are used by most of the Controllers.

Every request the server receives is redirected to [`routes/index.php`](./routes/index.php) and is handled by the [router](./routes/routes.php). The route with the same name as the request's end-point is called and it selects the appropriate controller and method to call based on the request's properties.

All interactions with the database are made through [models](./models) using the database [abstraction layer](./db/database.php).

### Front-end
The front-end is entirely contained in the [public](./public) directory. All third-party libraries are included via CDN. One of the requirements of this project was that there should be a single [`index.html`](./public/index.html) file linking to external JavaScript and CSS files. Therefore, no sampling or separation was made and no embedded PHP was used. All the HTML code can be found in a [single file](./public/index.html).

For the [JavaScripts](./public/javascripts), a separation of concerns was implemented. The code is highly modular and uses many features of ES6. jQuery is used for DOM manipulation only when there are significant savings in the amount of code for a feature.

Libraries:
* jQuery UI is used to implement auto-complete
* Bootstrap is used for most of the styling and layout, plus the construction all modals
* Chart.js is used for the chart in the Chat Hype Report

## Environment
The app is deployed in an AWS EC2 instance with the following configuration
* Amazon Linux AMI 2017.09
* Apache 2.4.27 (Amazon)
* MySQL Ver 14.14 Distrib 5.7.21
* PHP 7.1.13

## Running locally
To run the project locally, make sure that you have the right versions of Apache, MySQL and PHP installed. You will also need [Composer](https://getcomposer.org) to install the Google API.
1. Clone the repository
2. Run `composer install` in the root directory.
3. Make sure Apache is configured to allow the use of `.htaccess` files in this folder
4. That's it!

## Licence
This project is available under the MIT License. For more information, check the [LICENSE](./LICENSE) file.
