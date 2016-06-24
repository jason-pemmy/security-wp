WP Security Dashboard
===

Why?
---
We have dozens of WordPress websites that we have launched in the past, and we continue to launch one new website per month. The sheer size of our portfolio (50+ WordPress installs) means that we can't manage it manually (by having someone check each site manually).

We did some market research and did not find a paid solution that would take care of alerting us of vulnerabilities in our WordPress websites. 

What?
---

And so, we're building our own homegrown dashboard that will display all of the websites we are monitoring and show us the vulnerability status for each.

Who?
---

This will be used by technical and non technical staff at tbk Creative. Our Account Managers will be using this to propose upgrade packages to clients, so it must be easy to use for them and for our technical team.

How?
---

This project has 3 main components

**WordPress Backend**

This will manage the content for the dashboard. I imagine 1 custom post type (Website), with custom fields as required.

**RESTful API**

Let's leverage the WP-API plugin to provide us a RESTful API. The API needs to return the list of websites and their status. A secondary call should be made available to get the full, detailed, list of vulnerabilities found in the website. Someone would have to request this specifically (likely via a VIEW DETAILS link).

**Angular Front End**

The front-end of this website will be a 1 page dashboard.   Here are my requirements:

1. Must be simple, and gorgeous in its simplicity.
2. Must have a simple nav bar at top, no menu links, just the tbk Creative logo and the dashboard name (WordPress Security Dashboard)
2. Must list all the websites that we are monitoring.
	* Client Name
	* Website URL
	* Website Status (Vulnerable or Secure)
3. Must have a filter bar above the list to live filter the list of websites
4. Must have a toggle to filter on status too (Show only Vulnerable Website OR Show only secure Websites)
5. Must be optimized for Mobile Phones, Tablets and Desktops (in other words, I must enjoy using it on all three types of devices)


Design Choices
---

I have some specific requirements for the design of the page:

1. Follow the Material Design for Angular best practices (and use the library that they make available too): 
	* https://material.angularjs.org/latest/
	* https://www.npmjs.com/package/angular-material 
4. It must be accessible and meet WCAG 2.0 Level AA.
5. It must use at most 2 web fonts, we can use Typekit for this (and we can pick our fonts)
6. Use the tbk Creative logo in the navbar
7. The design must be Retina ready, so no images unless we account for retina with the images (using @2x images)

Technical Requirements
---

1. Use the following repo for storing your code: 
	* git@gitlab.tbkdev.com:tbk-devs/security.tbkdev.com.git
	* http://gitlab.tbkdev.com/tbk-devs/security.tbkdev.com
2. 


