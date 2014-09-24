<!--mbrousseau, Jan, 2014
Entry form for Sound Map, utilizes js location picker by https://github.com/rolos79/locationpicker-->
<?php 
//Allow headers in any order
ob_start(); 
//Grab the database info
require("phpsqlajax_dbinfo.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);	

//Expiry headers
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

//The session cookie from BLTI security code
include '/var/www/html/elearn-admin/functions.php';

// Load up the LTI Support code - from https://code.google.com/p/ims-dev/
require_once 'blti.php';

// Initialize, all secrets are 'secret', do not set session, and do not redirect
$context = new BLTI("%SECRET%", false, false);

?>

<!DOCTYPE html>
<html>
<head lang="en">
	<meta charset="utf-8">
	<title>Sound Map Point Entry</title>
	<!--The javascript for the location picker-->
	<link type="text/css" rel="stylesheet" href="css/locationpicker.css" />
	<!--The javascript for the google map-->
	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>
	<!--JQuery inclusion stuff-->
	 <link rel="stylesheet" href="https://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">
	  <script src="https://code.jquery.com/jquery-1.9.1.js"></script>
	  <script src="https://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
	  <link rel="stylesheet" href="css/modal-style.css">
	<!--Modal and verification script-->
	<script>
	  $(function() {
		var name = $( "#locName" ),
		  url = $( "#url" ),
		  username = $( "#userName" ),
		  latlong = $( "#picker" ),
		  desc = $( "#desc" ),
		  allFields = $( [] ).add( name ).add( url ).add( username ).add( latlong ).add( desc ),
		  tips = $( ".validateTips" );	 
		function updateTips( t ) {
		  tips
			.text( t )
			.addClass( "ui-state-highlight" );
		  setTimeout(function() {
			tips.removeClass( "ui-state-highlight", 1500 );
		  }, 500 );
		}	 
		function checkLength( o, n, min, max ) {
		  if ( o.val().length > max || o.val().length < min ) {
			o.addClass( "ui-state-error" );
			updateTips( "Length of " + n + " must be between " +
			  min + " and " + max + "." );
			return false;
		  } else {
			return true;
		  }
		}	 
    $( "#dialog-form" ).dialog({
      autoOpen: false,
      height: 520,
      width: 500,
	  position:['middle',50],
	  closeOnEscape: false,
      modal: true,
      buttons: {
        "Create a Sound Point": function() {
          var bValid = true;
          allFields.removeClass( "ui-state-error" );
			 
			 //Test output of field information
			console.log("Name: "+name.val()+" Length: "+name.val().length);
			console.log("Url: "+url.val()+" Length: "+url.val().length);
			console.log("Username: "+username.val()+" Length: "+username.val().length);
			console.log("Latlong: "+latlong.val()+" Length: "+latlong.val().length);
			console.log("Desc: "+desc.val()+" Length: "+desc.val().length);
			
			  //allFields = $( [] ).add( name ).add( url ).add( username ).add( latlong ).add( desc ),
			  bValid = bValid && checkLength( name, "location name", 3, 50 );
			  bValid = bValid && checkLength( url, "url", 6, 80 );
			  bValid = bValid && checkLength( username, "name", 3, 50 );
			  bValid = bValid && checkLength( latlong, "geoLocation", 3, 50 );
			  bValid = bValid && checkLength( desc, "description", 6, 80 );
	 
			  if ( bValid ) {
				$( "#users tbody" ).append( "<tr>" +
				  "<td>" + name.val() + "</td>" +
				  "<td>" + url.val() + "</td>" +
				  "<td>" + username.val() + "</td>" +
				  "<td>" + latlong.val() + "</td>" +
				  "<td>" + desc.val() + "</td>" +
				"</tr>" );
				$( "#soundSubmit" ).click();
				$( this ).dialog( "close" );
				
			  }
			},
			Cancel: function() {
			  $( this ).dialog( "close" );
			}
		  },
		  close: function() {
			allFields.val( "" ).removeClass( "ui-state-error" );
			username.val("");
		  }
		});
	 
		$( "#dialog-form-rem" ).dialog({
		  autoOpen: false,
		  height: 520,
		  width: 500,
		  position:['middle',50],
		  modal: true});
	 
		$( "#picker" ).click(function() {
		  $( "#lp-search" ).focus();
		});
	 
		$( "#create-point" )
		  .button()
		  .click(function() {
			$( "#dialog-form" ).dialog( "open" );
		  });
		  
		$( "#remove-point" )
		  .button()
		  .click(function() {
			$( "#dialog-form-rem" ).dialog( "open" );
		  });  
	  });
	</script>
		
	<!--Script for the location picker-->
    <script type="text/javascript">
    //<![CDATA[
    var customIcons = {
	  audio: {
		icon: 'img/sound_yellow2.png'
      }
    };
    function load() {
      var map = new google.maps.Map(document.getElementById("map"), {
        center: new google.maps.LatLng(43.119953, -79.246552),
        zoom: 16,
        mapTypeId: google.maps.MapTypeId.HYBRID
      });
      var infoWindow = new google.maps.InfoWindow;
	  
      // Change this depending on the name of your PHP file
      downloadUrl("genxml.php", function(data) {
        var xml = data.responseXML;
        var markers = xml.documentElement.getElementsByTagName("marker");
        for (var i = 0; i < markers.length; i++) {
          var name = markers[i].getAttribute("name");
          var address = markers[i].getAttribute("address");
          var type = markers[i].getAttribute("type");
		  var desc = markers[i].getAttribute("desc");
		  var url = markers[i].getAttribute("url");
		  var createdby = markers[i].getAttribute("createdby");
		  //Check if there is a correct protocol selected for the url
		  var startUrl = url.indexOf("http://");
		  var startUrls = url.indexOf("https://");
		  //If there isn't append http
		  if(startUrl == -1 && startUrls == -1){
			url = "http://"+url;
		  }
		  
          var point = new google.maps.LatLng(
              parseFloat(markers[i].getAttribute("lat")),
              parseFloat(markers[i].getAttribute("lng")));
          var html = "<div style='min-height:180px;'><b>" + name + "</b> <br/><i>Created by: " + createdby + "</i><br /><br/>" + desc + '<br /><a href="' + url + '" target="_blank">' + url + '</a></div>';
          var icon = customIcons[type] || {};
          var marker = new google.maps.Marker({
            map: map,
            position: point,
            icon: icon.icon
          });
          bindInfoWindow(marker, map, infoWindow, html);
        }
      });
    }
    function bindInfoWindow(marker, map, infoWindow, html) {
      
	  google.maps.event.addListener(marker, 'click', function() {
        infoWindow.setContent(html);
		infoWindow.setOptions({maxWidth: 200});
        infoWindow.open(map, marker);
      });
    }
    function downloadUrl(url, callback) {
      var request = window.ActiveXObject ?
          new ActiveXObject('Microsoft.XMLHTTP') :
          new XMLHttpRequest;

      request.onreadystatechange = function() {
        if (request.readyState == 4) {
          request.onreadystatechange = doNothing;
          callback(request, request.status);
        }
      };

      request.open('GET', url, true);
      request.send(null);
    }
    function doNothing() {}
    //]]>
	</script>	
</head>
<body onload="load()">

    <!--Wrapper div for padding-->
	<div id = "wrapper">

	 
	<div style="float:left"><h2>Sound Map</h2></div>

	<!--SOUND MAP POINT ENTRY - Add new points on the map-->
	<div style="float:right"><button id="remove-point">- Remove Sound Point</button></div>
	<div style="float:right"><button id="create-point">+ Create new Sound Point</button></div>
	
	
	<!--SOUND MAP - The actual sound map-->
	<div id="map" style="width: 100%; height: 800px"></div>

	
	<!--Modal for creating new points-->
	<div id="dialog-form" title="Add new sound point">
	<p class="validateTips">All form fields are required.</p>	
	<!--Main submission form with loc picker-->
	<form action="index.php" id="audioForm" method="POST">
	<label for='locName'>Location Name:</label>
	<input type='text' name='locName' id='locName'/><br />
	<label for='url'>URL to Sound File:</label>
	<input type='text' name='url' id='url'/><br />
	<label for='userName'>Username:</label>
	<input type='text' name='userName' id='userName' value="<?php echo $user['username'];?>" value readonly/><br />	
	<label for='picker'>geoLocation:</label>
	<input class='latlng' type='text' name='picker' id='picker' value readonly/>
	<button type="button" value="Close">Close</button><br />
	<label for='desc'>Item Description:</label><br />
	<textarea rows='8' cols='60' name='desc' id='desc'></textarea><br />
	<input style="visibility:hidden" type='submit' value='Add Entry' id ="soundSubmit" name="soundSubmit"/>
	</form>
	</div>
	
	<!--Modal for removing points-->
	<div id="dialog-form-rem" title="Remove a sound point">
	<p class="validateTips">This is a listing of all of the sound point you've created. <br />Choose remove on the point you'd like to delete. <br />There is no way to reverse this process.</p>	
	<!--Main submission form with loc picker-->
	<form action="index.php" id="removeForm" name="removeForm" method="POST">
	<?php 
	//Get the sound points the current user has created and present them for potential deletion
	$db2 = new PDO('mysql:host=localhost;dbname='.$database.';charset=utf8', $username, $password);
		if (!$db2) {
		  die('Failed to connect to DB : ' . mysql_error());
		}

		// Select all the rows in the markers table
		$stmt2 = $db2->prepare('SELECT * FROM `markers` WHERE `createdby` = "'.$user['username'].'"');
		$stmt2->execute();
		//Pull out all the points the user has created
    	while (($row = $stmt2->fetch(PDO::FETCH_ASSOC)) !== false) {
			//print_r($row);
			echo $row['name'].' - <input style="display:inline" type="checkbox" value="'.$row['id'].'" id ="check-'.$row['id'].'" name="check-'.$row['id'].'">
			<br />';
		}	
	?>
	<input type='button' value='Remove Item(s)' onclick='$( "#soundRemove" ).click()'/>
	<input style="visibility:hidden" type='submit' value='Remove Entry' id ="soundRemove" name="soundRemove"/>
	</form>
	</div>
	
	<!--Load the location picker javascript-->
	<script src="js/jquery.locationpicker.js"></script>
	<script>
		$('.latlng').locationPicker();
	</script>
	
	<?php
	//If the user has submitted the form to add a point
	if(isset($_POST["soundSubmit"])){
		
		// Opens a connection to a MySQL server using PDO
		$db = new PDO('mysql:host=localhost;dbname='.$database.';charset=utf8', $username, $password);
		if (!$db) {
		  die('Failed to connect to DB : ' . mysql_error());
		}
		
		//Pull apart the lat and long 
		$latLong = explode(",", $_POST['picker']);
		
		// Select all the rows in the markers table
		$stmt = $db->prepare("INSERT INTO markers(`name`,`lat`,`lng`,`desc`,`url`,`createdby`) VALUES(?,?,?,?,?,?)");

		//Try catch the prepared statment or throw the mysql error
		try {
    			$stmt->execute(array($_POST["locName"], $latLong[0], $latLong[1], htmlspecialchars($_POST["desc"], ENT_QUOTES), htmlspecialchars($_POST["url"], ENT_QUOTES), $_POST["userName"], $found_cookie['context_id']));
		} catch(PDOException $ex) {
			echo "Unable to: "; //user friendly message
			echo $ex->getMessage();
		}
		header("Location: index.php?");
	}
	
	//If the user has submitted the form to remove a point
		if(isset($_POST["soundRemove"])){
		
		// Opens a connection to a MySQL server using PDO
		$db = new PDO('mysql:host=localhost;dbname='.$database.';charset=utf8', $username, $password);
		if (!$db) {
		  die('Failed to connect to DB : ' . mysql_error());
		}
		//Run through all the submitted items for removal
		foreach ($_POST as &$check){
			if($check == "Remove Entry"){
				break;
			}
			// Select all the rows in the markers table
			$stmt = $db->prepare("DELETE FROM `markers` WHERE `id` = ?");

			//Try catch the prepared statment or throw the mysql error
			try {
				$stmt->execute(array($check));
			} catch(PDOException $ex) {
				echo "Unable to: "; //user friendly message
				echo $ex->getMessage();
			}
		}
		//Push the user to the map with a blank get pass to stop multiple submits/removals
		header("Location: index.php?");
	}
	
	
	?>	
	
	</div>
</body>
</html>
