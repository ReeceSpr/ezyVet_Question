<?php
/* ######## Interpreted Instructions ########
Simple PHP Class for basic shopping cart

Characteristics: 
Presistence through refresh/loads.
All prices to 2dp.

Properties:
x$cart - array of items in cart. Product Name, price, quantity, total, remove link.

Methods:
xAdd(p) - Adds P to cart. If P is in cart then quantity++ over adding new entry.
xView() - Returns all products in cart in viewable format.
xRemove(p) - Removes P from cart. If P is in the cart more then once quantity-- over removing entry.
xTotal() - Returns total price of products in cart. 

Assumptions:
Product List is not a part of cart.
We want to keep state but out of simplicity sake I will use sessions over a DB.
"Must be able to view current products in cart", could suggest there is a state where you cannot.
*/

// ######## please do not alter the following code ########
$products = [
	[ "name" => "Sledgehammer", "price" => 125.75 ],
	[ "name" => "Axe", "price" => 190.50 ],
	[ "name" => "Bandsaw", "price" => 562.131 ],
	[ "name" => "Chisel", "price" => 12.9 ],
	[ "name" => "Hacksaw", "price" => 18.45 ],
];
// ########################################################


class Cart {
	// ######## Properties ########
	private $cart = [];

	// ######## Methods ########

	//Constructor - Ensures a session is running, if old cart exsists import it in.
	function __construct() {
		if (!isset($_SESSION)) {
    		session_start();
		}
		if(isset($_SESSION['cart'])){
			$this->cart = unserialize($_SESSION['cart']);
		}
    }
    /*Adds a product to the cart, saves adjustment to $_SESSION['cart'].
    @Params:
	$addName - Name of product to add.
	$products - Array of product details.
	 - Searchs through the $cart to see if it contains a product with the same name.
	 	- Increase quantity, save, return.
	 - Searchs through $products to find product details of $addName.
	 	- Creates new entry in $cart and adds details, return.	
	*/
	function add($addName, $products) {
		foreach ($products as ["name" => $productName, "price" => $productPrice]) {
			if($addName == $productName){
				foreach ($this->cart as ["name" => $cartName, "price" => $cartPrice, "quantity" => $cartQuantity]) {
					if($productName == $cartName){
						//Duplicate Exsists
						$cartKey = [ "name" => $cartName, "price" => $cartPrice, "quantity" => $cartQuantity ];
						$cartIndex = array_search($cartKey, $this->cart);
						$this->cart[$cartIndex]["quantity"] = $cartQuantity+1;
						$_SESSION['cart'] = serialize($this->cart);
						return;
					}
				}
				//Duplicate Does Not Exsist
				$productKey = [ "name" => $productName, "price" => $productPrice ];
				$productIndex = array_search($productKey, $products);
				$addItem = $products[$productIndex];
				$addItem["quantity"] = 1;
				array_push($this->cart, $addItem );
				$_SESSION['cart'] = serialize($this->cart);
				return;
			}
		}
	}


	/*Removes a product to the cart, saves adjustment to $_SESSION['cart'].
    @Params:
	$removeName - Name of product to remove.
	$products - Array of product details.
	 - Searchs through the $cart to find product with same name.
	 	- If 1 or less delete entry in cart, return.
	 	- If 2 or more adjust quantity down of entry in cart, return.
	*/
	function remove($removeName, $products) {
		foreach ($this->cart as ["name" => $cartName, "price" => $cartPrice, "quantity" => $cartQuantity]) {
			if($removeName == $cartName){
					$cartKey = [ "name" => $cartName, "price" => $cartPrice, "quantity" => $cartQuantity ];
 					$cartIndex = array_search($cartKey, $this->cart);				
 					if($cartQuantity<=1){
					//One exsists
					unset($this->cart[$cartIndex]);
					$_SESSION['cart'] = serialize($this->cart);
					return;
				} else{
					//More then one exsists
					$this->cart[$cartIndex]["quantity"] = $cartQuantity-1;
					$_SESSION['cart'] = serialize($this->cart);
					return;
				}
			}
		}
	}

	/* Creates a HTML string to be echod to show contents of cart. Rounds prices.
	 */
	public function view() {
		$output = "";
		$output = $output."Current Cart: <br> <form action='index.php' method='post'>";
    	foreach ($this->cart as ["name" => $name, "price" => $price, "quantity" => $quantity]) {
    		$roundPrice = number_format((float)$price, 2, '.', '');
    		$output = $output."Name: $name, price: $roundPrice, Quantity: $quantity 
    		<input id='$name' type='submit' name='$name' value='Remove'> 
    		<br>";
		}
		return $output;
	}

    /* Creates a HTML string to be echod to show total price of cart. Rounds price.
	 */
	public function total() {
		$output = 0;
		foreach ($this->cart as ["price" => $price, "quantity" => $quantity]) {
			$output+=($price*$quantity);
		}
		return number_format((float)$output, 2, '.', '');
	}
}

//######## Example Of How Class Can Be Used ########//
//New Object
$simpleCart = new Cart();

/*Check if user has made a action.
- If they have, save in $_SESSION, and unset $_POST. This will keep its “state” during page loads / refreshes
*/
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['postdata'] = $_POST;
    unset($_POST);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

//Object is being controlled by this block. Post requests determine what/if methods are called on object.
if(isset($_SESSION['postdata'])){
	foreach ( $_SESSION['postdata'] as $key => $value) {
	 	switch ($value) {
	 		case 'Add':
	 			# code...
	 			$simpleCart->add($key, $products);
	 			unset($_SESSION['postdata']);
	 			break;
	 		
	 		case 'Remove':
	 			# code...
	 			$simpleCart->remove($key, $products);
	 			unset($_SESSION['postdata']);
	 			break;

	 		default:
	 			# code...
	 			break;
	 	}
	}
}

// IO // 
echo "<h1> Simple Cart Example </h1>";
echo "<form action='index.php' method='post'>";
//Prints all products.
foreach ($products as ["name" => $name, "price" => $price]) {
	$roundPrice = number_format((float)$price, 2, '.', '');
	echo "Name: $name, price: $roundPrice , 
	<input id='$name' type='submit' name='$name' value='Add'>
	<br>";
}
echo "<br>";
echo $simpleCart->view();
echo "Total: $";
echo $simpleCart->total();
?>