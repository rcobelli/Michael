<script>
    $(document).ready(function($) {
        $(".clickable-row").click(function() {
            window.location = $(this).data("href");
        });
        $('#dtBasicExample').DataTable({"pageLength": 25});
        $('.dataTables_length').addClass('bs-select');
    });
</script>
</body>
</html>
