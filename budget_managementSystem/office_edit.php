<?php
require 'db_connect.php';

$department = isset($_GET['department']) ? $_GET['department'] : '';
$acc_name = isset($_GET['acc_name']) ? $_GET['acc_name'] : '';

    // Prepare the SQL statement acc_name
    $department_sql = "SELECT identifier, office FROM tbl_budget WHERE identifier != ?";
    $stmt = $conn->prepare($department_sql);
    $stmt->bind_param("s", $department); // "s" means the database expects a string
    $stmt->execute();
    $departments = $stmt->get_result();

    $acc_title_sql = "SELECT acc_code, acc_name FROM tbl_budget WHERE identifier = ?";
    $stmt = $conn->prepare($acc_title_sql);
    $stmt->bind_param('s', $department);
    $stmt->execute();
    $resultAcc = $stmt->get_result();
?>

<!-- Edit Modal Structure -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" style="padding-left: 100px; width: 70%;">
    <div class="modal-content">
      <form id="editForm" action="office_save.php" method="POST">
        <div class="modal-header">
          <input type="text" class="form-control" id="editOffice" name="office" style="border:none;">
        </div>
        <div class="modal-body">
          <!-- Hidden field to store the record ID -->
          <input type="hidden" id="editId" name="id">

          <!-- Account Name and Account Code in one line -->
          <div class="row">
            <div class="col-md-9 mb-3">
              <label for="editAccName" class="form-label">Account Name</label>
              <input type="text" class="form-control" id="editAccName" name="acc_name" disabled>
            </div>
            <div class="col-md-3 mb-3">
              <label for="editAccCode" class="form-label">Account Code</label>
              <input type="text" class="form-control" id="editAccCode" name="acc_code" disabled>
            </div>
          </div>

          <!-- Budget, Balance, Expense, ARO in one line -->
          <div class="row">
            <div class="col-md-3 mb-3">
              <label for="editBudget" class="form-label">Budget</label>
              <input type="number" class="form-control" id="editBudget" name="budget" disabled>
            </div>
            <div class="col-md-3 mb-3">
              <label for="editBalance" class="form-label">Balance</label>
              <input type="number" class="form-control" id="editBalance" name="balance" disabled>
            </div>
            <div class="col-md-3 mb-3">
              <label for="editExpense" class="form-label">Expense</label>
              <input type="number" class="form-control" id="editExpense" name="expense" disabled>
            </div>
            <div class="col-md-3 mb-3">
              <label for="editARO" class="form-label">ARO</label>
              <input type="text" class="form-control" id="editARO" name="aro" disabled>
            </div>
          </div>

          <!-- Supplemental, Realignment, and Reprogram fields -->
          <div class="row">
          <button type="button" class="btn btn-info" id="reaButton" style="font-size:12pt; margin-left:auto; margin-right:auto;">Add Realignment</button>
              <button type="button" class="btn btn-info" id="supButton" style="font-size:12pt; margin-left:auto; margin-right:auto;">Add Supplemental</button>
          </div>
<br>
<!-- Hidden Supplemental Section -->
<div class="row" id="supplemental" style="display: none;"> <!-- Initially hidden -->
    <div class="col-md-8 mb-3">
        <label for="department" class="form-label">Department</label>
        <select class="form-control" id="department" name="department">
            <option value="">-- Select Office --</option>
            <?php
            if ($departments->num_rows > 0) {
                while ($row = $departments->fetch_assoc()) {
                    echo "<option value='{$row['office']}'>{$row['office']}</option>";
                }
            } else {
                echo "<option value=''>No departments available</option>";
            }
            ?>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="editSupplemental" class="form-label">Supplemental Amount</label>
        <input type="number" class="form-control" id="editSupplemental" name="supplemental">
    </div>
</div>
<!-- Hidden Realignment Section -->
<div class="row" id="realignment" style="display: none;"> <!-- Initially hidden -->
    <div class="col-md-8 mb-3">
        <label for="acc_name2" class="form-label">Account Name</label>
        <select class="form-control" id="acc_name2" name="acc_name2" required onchange="updateAccountDetails()">
                <option value="">-- Select Account Name --</option>
                <?php              
                if ($resultAcc->num_rows > 0) {
                    while ($row = $resultAcc->fetch_assoc()) {
                        echo "<option value='{$row['acc_name']}' data-acc-code='{$row['acc_code']}'>{$row['acc_name']}</option>";
                    }
                } else {
                    echo "<option value=''>No available</option>";
                }
                ?>
            </select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="editRealignment" class="form-label">Realignment Amount</label>
        <input type="number" class="form-control" id="editRealignment" name="realignment">
    </div>
</div>

        </div>
        <!-- <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="font-size:12pt; width:40%; margin-left:auto; margin-right:auto;">Close</button>
          <button type="submit" class="btn btn-primary" style="font-size:12pt; width:40%; margin-left:auto; margin-right:auto;">Save Changes</button>
        </div> -->
        <!-- Update Buttons in the Modal -->
<div class="modal-footer">
    <button type="submit" class="btn btn-primary" name="submitSupplemental" id="submitSupplemental" style="font-size:12pt; width:40%; margin-left:auto; margin-right:auto; display: none;">Add Supplemental</button>
    <button type="submit" class="btn btn-primary" name="submitRealignment" id="submitRealignment" style="font-size:12pt; width:40%; margin-left:auto; margin-right:auto; display: none;">Add Realignment</button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="font-size:12pt; width:40%; margin-left:auto; margin-right:auto;">Close</button>
</div>
      </form>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $("#supButton").on("click", function() {
        $("#supplemental").show();
        $("#realignment").hide();
        $("#submitRealignment").hide();
        $("#submitSupplemental").show();
    });
    $("#reaButton").on("click", function() {
        $("#realignment").show();
        $("#supplemental").hide();
        $("#submitSupplemental").hide();
        $("#submitRealignment").show();
    });
});
</script>
