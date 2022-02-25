<form method="post">
    <input type="hidden" name="action" value="newJob">
    <input type="hidden" name="user_id" value="<?php echo $_SESSION['id']; ?>">
    <input type="hidden" name="contact_id" value="<?php echo $_GET['contact_id']; ?>">
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="lastContact">Company:</label>
            <input type="text" id="lastContact" class="form-control" name="company">
        </div>
        <div class="form-group col-md-6">
            <label for="lastContactDetail">Title:</label>
            <input class="form-control" id="lastContactDetail" type="text" name="title">
        </div>
    </div>
    <div class="form-row">
        <div class="col text-center">
            <button type="submit" class="btn btn-primary mt-3">Submit</button>
        </div>
    </div>
</form>