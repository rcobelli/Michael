<form method="post">
    <input type="hidden" name="action" value="newAction">
    <input type="hidden" name="user_id" value="<?php echo $_SESSION['id']; ?>">
    <input type="hidden" name="contact_id" value="<?php echo $_GET['contact_id']; ?>">
    <div class="form-row">
        <div class="form-group col-md-3">
            <label for="lastContact">Date:</label>
            <input type="date" id="lastContact" class="form-control" placeholder="01/02/2022" name="date">
        </div>
        <div class="form-group col-md-9">
            <label for="lastContactDetail">Details:</label>
            <textarea class="form-control" id="lastContactDetail" rows="2" name="title"></textarea>
        </div>
    </div>
    <div class="form-row">
        <div class="col text-center">
            <button type="submit" class="btn btn-primary mt-3">Submit</button>
        </div>
    </div>
</form>