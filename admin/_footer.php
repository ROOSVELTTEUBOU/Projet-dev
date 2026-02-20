    </main>

    <footer class="site-footer">
      <p>&copy; <?php echo date('Y'); ?> Appecom - Portail administrateur</p>
    </footer>
  </div>
</body>
</html>
<?php
if (isset($adminDb) && $adminDb instanceof mysqli) {
    $adminDb->close();
}
?>
