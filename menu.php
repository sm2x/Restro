<?php 	
	require 'essentials.php';
	if (!checkSession()) {
		header('Location:index.php'); 
	}
	else{
		loadLayout("Restro - Browse Menu", "Home");	

	

	// Ignore errors
	ini_set('display_errors', 0); 


	// Get the list of all foods available from the database
	$query = "MATCH (n:FOOD) RETURN n"; 
	$results = $client->run($query);


	$_SESSION['tick'] = 0; 
?>


<script type="text/javascript">
	// Ajax request for menu	
    function getData(itemName, category = false){
      var xmlhttp = new XMLHttpRequest(); 
      xmlhttp.onreadystatechange = function(){
        if (this.readyState == 4 & this.status == 200) {
          $('#menuArea').html(this.responseText);           
        }
      }; 


      // If category part has been clicked
      if (category) {
      		xmlhttp.open("GET", "response.php?category="+itemName, true);
      }

      else{
      	if (itemName != "all") {
	      	xmlhttp.open("GET", "response.php?itemName="+itemName, true);
      	}
	    else{
      		xmlhttp.open("GET", "response.php?all", true);
 	 	}	
      }

      
      
      xmlhttp.send(); 

    }

</script>


	<div class="container" style = "
	    background-color: #7f3232;
	    padding: .5em 3em;
	    color: white;
	    margin-bottom: 1em;
	    width: 100%;
">
		<h4 class = 'text-center'><i class="fas fa-coffee"></i>  Ordering from Table - <?php echo $_SESSION['table']; ?></h4>
	
	</div>




<div class="container-fluid">

	<!-- LOG OUT -->
	<?php 
		if (isset($_GET['logout'])) {
			session_destroy();
			echo "<script>window.location = 'index.php';</script>";
		}
	?>
	<!-- END OF LOG OUT -->


	<div class = 'menu-bar'>
		<h3 data-toggle = 'tooltip' data-placement = 'bottom' title = '' class = 'padding pull-left pointer' onclick = 'getData("all")'>Menu</h3>

		<!-- Logout -->		
		<h4 name = "logout" data-toggle = 'tooltip' data-placement = 'bottom' title = 'Logout' onclick = "var a = confirm('Are you sure?'); if(a) window.location = 'menu.php?logout';" class="cart link"><span class = 'glyphicon glyphicon-log-out'></span></h4>

		<!-- Cart modal -->
		<h4 class = 'cart' data-toggle="modal" href='#modal-id'>
			<span class = 'glyphicon glyphicon-shopping-cart'></span></h4>

		<!-- Search -->
		<h4 class = "cart link pointer" data-toggle="popover" data-html = "true" title="Search" data-placement = "bottom" data-content = '
				
					<div class = "input-group">
						<input name = "itemName" onkeyup = "getData(this.value);" class = "form-control" type = "text">
						
					</div>	
				
					<hr>
					<p>Filter by</p>
					<div class = "input-group-sm">
						<select onchange = "getData(this.value, true);" name="category" class="form-control">
							<option  value = "" selected>Select category</option>
						<?php 
							$res = $client->run("MATCH (n:CATEGORY) RETURN n.name as name");

							foreach($res->getRecords() as $val){

								echo "<option value = ".$val->value("name").">".$val->value("name")."</option>"; 
							}
						?>
						</select>
					</div>	
					<br>
				'
			>
			<span class = 'glyphicon glyphicon-search'></span>			
		</h4>			
		<!-- End of search -->


		<!-- Cart -->
		<div class="modal fade" id="modal-id">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title"><span class = 'glyphicon glyphicon-shopping-cart'></span></h4>
					</div>
					<form id = 'cartForm' action = 'confirmOrder.php' method = 'GET'>
						<div class="modal-body">
							<table class="table table-striped">
								<thead>
									<tr>
										<th>Item</th>
										<th>Quantity</th>	
										<th>Price ($)</th>									
										<th></th>
									</tr>
								</thead>

								
									<tbody id = 'cart'>
										<tr>
											<td><h3>Existing orders</h3></td>
											<td><h3></h3></td>
											<td><h3></h3></td>
										</tr>										
										<!-- View placed orders -->
										<?php 											
											$size = sizeof($_SESSION['orders']);
											
											for ($i=0; $i < $size; $i++) {		
												echo "<tr>";
													
													for ($j=0; $j < 3; $j++) { 
														echo "<td>".$_SESSION['orders'][$i][$j]."</td>";
													} 					
												echo "</tr>";							 
											}
										?>
										<tr>
											<td></td>
											<td></td>
											<td></td>
										</tr>
										<tr>
											<td><h3>Current orders</h3></td>
											<td><h3></h3></td>
											<td><h3></h3></td>
										</tr>
									</tbody>							
								
							</table>
							<hr>
							<h3>Total: $<span id = 'total_price'></span></h3>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
							<button type="button" id = 'confirm' class="btn btn-sm btn-primary">Confirm</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<!-- End of cart modal -->				
	</div>
	<!-- End of menu bar -->


	<?php 
		if (isset($_POST['itemName'])) {
			echo "<h4 class = 'padding pull-left'>Showing related results for ('".$_POST['itemName']."')</h4>";		
		}
		else if (isset($_POST['category'])) {
			if ($_POST['category'] != 'all') {
				echo "<h4 class = 'padding pull-left'>Sorting by ('".$_POST['category']."')</h4>";
			}
		}

	?>
	
	

	<hr class = 'menuHr'>
	
	<!-- Recommended List -->
	<div class="container custom pre-scrollable">
		<h2 id = 'rTitle'>Your recommendations</h2>
		<div id = 'recommendedList'>
			<h4>Nothing to recommend :(</h4>
		</div>
	</div>
	<!-- End of recommended list -->




	<!-- Menu -->
	<div class="container custom pre-scrollable" id = "menuArea">					
		<table class="table table-hover menu" id = 'orderTable'>
			<thead>
				<tr>
					<!-- <th>S.N</th> -->
					<th><h4>Item</h4></th>
					<th><h4>Price</h4></th>
					<th></th>
				</tr>
			</thead>

		

			<tbody>				
				
				
				<?php 
					$a = 1; 
					$id = 1;
					foreach ($results->getRecords() as $result) {
						 
						echo "<tr>";		
							// echo "<td><br>".$a++."</td>";
							echo "<td>";
				
							echo '<h5 id = "item" style = "cursor:pointer;" data-toggle="modal" href="#modal-id'.$id.'">';
				
								echo $result->get('n')->value('name');

							echo "</h5>";
							
							// End of modal head

							// echo "<p id = 'description' style = 'cursor:pointer;' data-toggle = 'modal' href = '#modal-id".$id."'>".$result->get('n')->value('description')."</p>";
							echo "<img data-toggle = 'modal' href = '#modal-id".$id."' class = 'menuImage' src = 'Images/".$result->get('n')->value('image1')."'>";


							// Modal body
							
							echo '<div class="modal fade" id="modal-id'.$id.'">'; ?>
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
											<h4 class="modal-title" style = "color:#484847;" ><?php echo $result->get('n')->value('name'); ?></h4>
										</div>
										<div class="modal-body">
											<?php 
													// Description
													echo "<h4>Description</h4>";
													echo "<p>".$result->get('n')->value('description')."</p>";

													$total_images = $result->get('n')->value('numImages'); 
									    			echo "<div class = 'food_images'>";
										    			for ($i=1; $i <= $total_images; $i++) { 
									    				
									    					echo "<img src = '".substr($result->get('n')->value('image'.$i), 3)."' >";
										    				
										    			}
									    			echo "</div>";
											?>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>								
										</div>
									</div>
								</div>
							</div>

				<?php
							// End of modal

							echo "</td>";

							// Price
							echo "<td>$".$result->get('n')->value('price')."</td>";
							
							// Checkbox
							echo "<td>";

								echo "<a id = 'order' class = 'btn btn-sm btn-default'><span class = 'fas fa-cart-arrow-down'></span></a>";
							echo "</td>";
						echo "</tr>";	
						$id++;
					}				
				?>					
			</tbody>
		</table>
				
		
	</div>
</div>

<!-- End of menu -->


<footer>
	<div class="container-fluid">
		
			<h4>Follow us at:</h4>
			<ul>
				<li><i style = 'color:white' class = 'fab fa-facebook fa-2x'></i></li>				
				<li><i style = 'color:white' class = 'fab fa-instagram fa-2x'></i></li>
				<li><i style = 'color:white' class = 'fab fa-twitter fa-2x'></i></li>
				<li><i style = 'color:white' class = 'fab fa-pinterest fa-2x'></i></li>					
			</ul>

			<p>&copy All Rights Reserved</p>
			
		
		
	</div>

</footer>
<?php 
	}
?>
