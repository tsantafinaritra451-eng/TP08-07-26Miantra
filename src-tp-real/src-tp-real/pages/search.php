<?php
    include('../inc/functions.php');

    $departments = get_all_departments();

    // Récupération des critères (?? '' évite le warning si le champ est absent)
    $dept_no = $_GET['dept_no'] ?? '';
    $name    = $_GET['name']    ?? '';
    $age_min = $_GET['age_min'] ?? '';
    $age_max = $_GET['age_max'] ?? '';

    // On ne lance la recherche que si le formulaire a été soumis
    $submitted = isset($_GET['dept_no']);
    $results   = $submitted ? search_employees($dept_no, $name, $age_min, $age_max) : array();
?>
<html>
    <head>
        <title>Recherche d'employés</title>
    </head>
    <body>
    <p><a href="index.php">&larr; Retour aux départements</a></p>
    <h1>Recherche d'employés</h1>

    <form method="get" action="search.php">
        <p>
            Département :
            <select name="dept_no">
                <option value="">— Tous —</option>
                <?php foreach ($departments as $d) { ?>
                    <option value="<?= $d['dept_no'] ?>" <?= $dept_no === $d['dept_no'] ? 'selected' : '' ?>>
                        <?= $d['dept_name'] ?>
                    </option>
                <?php } ?>
            </select>
        </p>
        <p>Nom de l'employé : <input type="text" name="name" value="<?= htmlspecialchars($name) ?>"></p>
        <p>Âge min : <input type="number" name="age_min" value="<?= htmlspecialchars($age_min) ?>"></p>
        <p>Âge max : <input type="number" name="age_max" value="<?= htmlspecialchars($age_max) ?>"></p>
        <p><input type="submit" value="Rechercher"></p>
    </form>

    <?php if ($submitted) { ?>
        <h2><?= count($results) ?> résultat(s)<?= count($results) === 200 ? ' (limité à 200)' : '' ?></h2>
        <table border="1">
            <tr>
                <th>N°</th>
                <th>Prénom</th>
                <th>Nom</th>
                <th>Genre</th>
                <th>Âge</th>
                <th>Département</th>
            </tr>
            <?php foreach ($results as $emp) { ?>
                <tr>
                    <td><a href="fiche.php?emp_no=<?= urlencode($emp['emp_no']) ?>"><?= $emp['emp_no'] ?></a></td>
                    <td><?= $emp['first_name'] ?></td>
                    <td><?= $emp['last_name'] ?></td>
                    <td><?= $emp['gender'] ?></td>
                    <td><?= $emp['age'] ?></td>
                    <td><?= $emp['dept_name'] ?></td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>
    </body>
</html>
