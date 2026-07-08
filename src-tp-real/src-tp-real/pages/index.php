<?php
    include('../inc/functions.php');
    $departments = get_all_departments();

?>		
<html>
    <head>
        <title>Les news</title>
    </head>
    <body>
    <h1>Liste des départements</h1>
    <p><a href="search.php">🔍 Rechercher un employé</a></p>
    <p><a href="stats.php">📊 Statistiques par emploi</a></p>
    <p><a href="dept_form.php">➕ Ajouter un département</a></p>
    <p><a href="emp_form.php">➕ Ajouter un employé</a></p>
 <table border="1">
    <tr>
        <th>Department Number</th>
        <th>Department Name</th>
        <th>Manager actuel</th>
        <th>Nombre d'employés</th>
        <th>Action</th>
    </tr>
    <?php foreach ($departments as $line) {?>
        <tr>
            <td><a href="employees.php?dept_no=<?= urlencode($line['dept_no']) ?>"><?= $line['dept_no']?></a></td>
            <td><?=$line['dept_name']?></td>
            <td><?= $line['manager_name'] ?? '—' ?></td>
            <td><?= $line['nb_employees'] ?></td>
            <td><a href="dept_form.php?dept_no=<?= urlencode($line['dept_no']) ?>">Éditer</a></td>
        </tr>
    <?php } ?>
    </table>

    </body>
</html>
