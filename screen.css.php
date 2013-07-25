<?php
require ('intercon_db.inc');
header("Content-type: text/css");
?>
body
{
	margin: 135px 0 0 0;
	padding: 0 7% 0 220px;
	background-image: url("PageBanner.png");
	background-repeat: no-repeat;
	background-color: #ffffff;
	background-position: 9px 5px;
      	color: #000000;
	font-family: sans-serif;
}

a img {
  border: 0;
}

.navbar
{
	position: absolute;
	z-index: 5;
	width: 192px;
	top: 140px;
	left: 9px;
	margin: 0;
	padding: 0;
}

.navbar ul.menu {
  margin-top: 16px;
  margin-bottom: 16px;
}

#game_admin {
  float: right;
  width: 150px;
}

ul.menu, ul.subhead, .menulike {
    margin: 0;
	  text-align: center;
	  list-style-type: none;
    margin-left: 0;
    padding: 0;
    border: 2px <?php echo COLOR_MENU_PUBLIC_FG; ?> solid;
    
    -moz-border-radius: 5px;
    -webkit-border-radius: 5px;
    border-radius: 5px;
    
    background-color: <?php  echo COLOR_MENU_PUBLIC_BG; ?>;
    background-image: -webkit-gradient(linear, left top, left bottom, from(#fff), to(<?php  echo COLOR_MENU_PUBLIC_BG; ?>));
    background-image: -moz-linear-gradient(top, #fff, <?php echo COLOR_MENU_PUBLIC_BG; ?>);

    -moz-box-shadow: 0px 0px 5px <?php echo COLOR_MENU_PUBLIC_FG; ?>;
    -webkit-box-shadow: 0px 0px 5px <?php echo COLOR_MENU_PUBLIC_FG; ?>;
    box-shadow: 0px 0px 5px <?php echo COLOR_MENU_PUBLIC_FG; ?>;
}

ul.menu.priv {
  -moz-box-shadow: 0px 0px 5px <?php echo COLOR_MENU_PRIV_FG; ?>;
  -webkit-box-shadow: 0px 0px 5px <?php echo COLOR_MENU_PRIV_FG; ?>;
  box-shadow: 0px 0px 5px <?php echo COLOR_MENU_PRIV_FG; ?>;
}

ul.subhead {
  border: 1px #666 solid;
  -moz-border-radius: 0;
  -webkit-border-radius: 0;
  border-radius: 0;
  
/*  -moz-box-shadow: 0px 0px 3px black;
  -webkit-box-shadow: 0px 0px 3px black;
  box-shadow: 0px 0px 3px black; */
  
  -moz-box-shadow: none;
  -webkit-box-shadow: none;
  box-shadow: none;
  
  background-color: #aaa;
  background-color: rgba(0, 0, 0, 0.1);
}

ul.loginBar {
  list-style-type: none;
  margin: 0;
  padding: 0;
  text-align: right;
  position: absolute;
  left: 9px;
  top: 130px;
  height: 22px;
  width: 850px;
  
  border-bottom: 1px <?php  echo COLOR_MENU_PRIV_FG; ?> solid;
}

ul.loginBar li {
  width: 200px;
  height: 22px;
  display: -moz-inline-stack;
  display: inline-block;
  text-align: right;
  padding-left: 5px;
  padding-right: 5px;
  
  <!--[if IE lt 8]>
  zoom: 1;
  *display: inline;
  <![endif]-->
}

ul.accountControl li a {
  font-size: 90%;
/*  font-weight: bold;
  text-align: right; */
  padding: 3px;
  display: block;
  text-decoration: none;
  color: black;
  padding-right: 18px !important;
  background-position: right center;
  background-repeat: no-repeat;
}

ul.accountControl.loginBar li a {
  padding-right: 22px !important;
}

ul.loginBar li {
  background-color: transparent !important;
}

ul.accountControl li.login a {
  background-image: url(door_open.png);
}

ul.accountControl li.profile a {
  background-image: url(user.png);
}

ul.accountControl li.logout a {
  background-image: url(door.png);
}

ul.accountControl li.register a {
  background-image: url(user_edit.png);
}

ul.accountControl li.bio a {
  background-image: url(book_edit.png);
}

/*
ul.menu li.subhead > a {
  text-align: left;
  font-weight: bold;
}

ul.menu ul.subhead {
  text-align: left;
  list-style-type: square;
  list-style-position: inside;
  margin-left: 0;
  padding-left: 10px;
  font-size: 90%;
}

ul.menu ul.subhead li {
  border-bottom: none;
}
*/

ul.menu li.subhead {
  font-size: 90%;
  padding: 3px;
}

ul.priv {
    border-color: <?php echo COLOR_MENU_PRIV_FG; ?>;

    background-color: <?php echo COLOR_MENU_PRIV_BG ?>;
    background-image: -webkit-gradient(linear, left top, left bottom, from(#fff), to(<?php echo COLOR_MENU_PRIV_BG; ?>));
    background-image: -moz-linear-gradient(top, #fff, <?php echo COLOR_MENU_PRIV_BG; ?>);

/*    box-shadow: 0px 0px 5px <?php echo COLOR_MENU_PRIV_FG; ?>;
    -moz-box-shadow: 0px 0px 5px <?php echo COLOR_MENU_PRIV_FG; ?>;
    -webkit-box-shadow: 0px 0px 5px <?php echo COLOR_MENU_PRIV_FG; ?>; */
}

ul.menu.links {
  border-color: #555;
  
  background-color: #aaa;
  background-image: -webkit-gradient(linear, left top, left bottom, from(#fff), to(#aaa));
  background-image: -moz-linear-gradient(top, #fff, #aaa);

  box-shadow: 0px 0px 5px #555;
  -moz-box-shadow: 0px 0px 5px #555;
  -webkit-box-shadow: 0px 0px 5px #555;
}

ul.menu.links li.title
{
	background-color: #555 !important;
}

ul.menu.links li {
    border-color: #555;
}

ul.menu li.expandable a {
  background-image: url(bullet_toggle_plus.png);
  background-repeat: no-repeat;
  background-position: right center;
  padding-right: 14px;
}

ul.menu li {
    border-bottom: 1px <?php echo COLOR_MENU_PUBLIC_FG; ?> solid;
}

ul.menu li a {
    display: block;
    padding: 3px;
    font-size: 90%;
}

ul.menu li a:hover, ul.accountControl li a:hover {
    background-color: rgba(255, 255, 0, 0.2);
}

ul.menu li a, ul.menu li a:visited {
    color: black;
    text-decoration: none;
}

ul.menu li.current a, ul.accountControl li.current {
  background-color: white;
  font-weight: bold;
}

ul.priv li {
    border-bottom-color: <?php echo COLOR_MENU_PRIV_FG; ?>;
}

ul.subhead li {
  border-bottom-color: #666;
}

ul.menu li:last-child {
    border-bottom: none;
}

ul.menu li.title, .menulike .title
{
	background-color: <?php echo COLOR_MENU_PUBLIC_FG; ?>;
	color: #FFFFFF;
	font-weight: bold;
        border-bottom: none;
}

ul.menu.priv li.title
{
	background-color: <?php echo COLOR_MENU_PRIV_FG; ?>;
	font-style: normal;
}

ul.menu li.external a {
  background-image: url(external.png);
  background-position: right center;
  background-repeat: no-repeat;
  padding-right: 13px;
}

ul.accountControl li.title
{
  font-style: italic;
  border-left: none;
}

ul.subhead li.title {
  background-color: #aaa;
  background-color: rgba(0, 0, 0, 0.3);
}

ul.subhead.priv li.title {
  font-style: normal !important;
  background-color: <?php  echo COLOR_MENU_PRIV_BG ?>;
}

ul.subhead li.title.current a {
  background-color: transparent;
}

ul.menu li.alert {
  background-color: rgba(255, 0, 0, 0.3);
}

ul.menu li.info {
  background-color: rgba(255, 255, 255, 0.5);
  font-size: 80%;
  padding: 2px;
}

.print_logo
{
	display: none;
}

.copyright
{
	font-size: small;
	text-align: center;
}

.print_copyright
{
	display: none;
}

p.dev_warning
{
	font-size: large;
	color: red;
	font-weight: bold;
}
