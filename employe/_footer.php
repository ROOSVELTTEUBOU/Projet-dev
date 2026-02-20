    </main>

    <footer class="site-footer">
      <p>&copy; <?php echo date('Y'); ?> Appecom - Portail employe</p>
    </footer>
  </div>
</body>
</html>
<?php
if (isset($employeDb) && $employeDb instanceof mysqli) {
    $employeDb->close();
}
?>

