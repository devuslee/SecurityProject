<?php
session_start(); // Ensure session is started
?>
<?php
require_once '../config.php';
include '../inc/dashHeader.php'; 
$bill_id = $_GET['bill_id'];
$staff_id = $_GET['staff_id'];
$member_id = $_GET['member_id'];
$reservation_id = $_GET['reservation_id'];
?>

<div class="container" style="margin-top: 15rem; margin-left: 4rem;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Bill (Credit Card Payment)</h3>
                </div>
                <div class="card-body">
                    <h5>Bill ID: <?php echo $bill_id; ?></h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Item ID</th>
                                    <th>Item Name</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
            <?php
            // Query to fetch cart items for the given bill_id
            $cart_query = "SELECT bi.*, m.item_name, m.item_price FROM bill_items bi
                           JOIN Menu m ON bi.item_id = m.item_id
                           WHERE bi.bill_id = '$bill_id'";
            $cart_result = mysqli_query($link, $cart_query);
            $cart_total = 0; //cart total
            $tax = 0.1; // 10% tax rate

            if ($cart_result && mysqli_num_rows($cart_result) > 0) {
                while ($cart_row = mysqli_fetch_assoc($cart_result)) {
                    $item_id = $cart_row['item_id'];
                    $item_name = $cart_row['item_name'];
                    $item_price = $cart_row['item_price'];
                    $quantity = $cart_row['quantity'];
                    $total = $item_price * $quantity;
                    $bill_item_id = $cart_row['bill_item_id'];
                    $cart_total += $total;
                    echo '<tr>';
                    echo '<td>' . $item_id . '</td>';
                    echo '<td>' . $item_name . '</td>';
                    echo '<td>RM ' . number_format($item_price,2) . '</td>';
                    echo '<td>' . $quantity . '</td>';
                    echo '<td>RM ' . number_format($total,2) . '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="6">No Items in Cart.</td></tr>';
            }
            ?>
        </tbody>
                        </table>
                    </div>
                    <hr>
                    <div class="text-right">
                        <?php 
                        echo "<strong>Total:</strong> RM " . number_format($cart_total, 2) . "<br>";
                        echo "<strong>Tax (10%):</strong> RM " . number_format($cart_total * $tax, 2) . "<br>";
                        $GRANDTOTAL = $tax * $cart_total + $cart_total;
                        echo "<strong>Grand Total:</strong> RM " . number_format($GRANDTOTAL, 2);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="card-payment" class="col-md-6 order-md-2" style="margin-top: 10rem; margin-right: 5rem;max-width: 40rem;">
    <div class="container-fluid pt-5 pl-3 pr-3">
        <h1>Ready to Pay?</h1>
        <form action="creditCard.php?bill_id=<?php echo $bill_id; ?>" method="post">
            <!-- Hidden input fields with hardcoded values -->
            <input type="hidden" id="cardNameField" name="cardName" value="John Doe">
            <input type="hidden" id="cardField" name="cardNumber" value="1234567890123456">
            <input type="hidden" id="expiryDate" name="expiryDate" value="12/2025">
            <input type="hidden" id="securityCode" name="securityCode" value="123">

            <!-- Hidden input fields for bill_id, staff_id, member_id, and reservation_id -->
            <input type="hidden" name="bill_id" value="<?php echo $bill_id; ?>">
            <input type="hidden" name="staff_id" value="<?php echo $staff_id; ?>">
            <input type="hidden" name="member_id" value="<?php echo $member_id; ?>">
            <input type="hidden" name="reservation_id" value="<?php echo $reservation_id; ?>">
            <input type="hidden" name="GRANDTOTAL" value="<?php echo $tax * $cart_total + $cart_total; ?>">

            <!-- Pay button -->
            <button type="submit" id="cardSubmit" class="btn btn-dark">Pay</button>
        </form>
    </div>
</div>

<?php include '../inc/dashFooter.php'; ?>
