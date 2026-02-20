    </main>

    <footer class="site-footer">
      <p>&copy; <?php echo date('Y'); ?> Appecom - Portail client</p>
    </footer>
  </div>
</body>
</html>
<?php
if (isset($clientDb) && $clientDb instanceof mysqli) {
    $clientDb->close();
}
?>
