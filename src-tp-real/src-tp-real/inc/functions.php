<?php
include_once 'connection.php';

function get_all_lines($sql){
    //echo $sql;
    $req = mysqli_query(dbconnect(),$sql );
    if (!$req) {
        die('Erreur SQL : ' . mysqli_error(dbconnect()));
    }
    $result = array();
    while ($line = mysqli_fetch_assoc($req)) {
        $result[] = $line;
    }
    mysqli_free_result($req);
    return $result;
}

function get_one_line($sql){

    $req = mysqli_query(dbconnect(),$sql );
    if (!$req) {
        die('Erreur SQL : ' . mysqli_error(dbconnect()));
    }
    $result = mysqli_fetch_assoc($req);
    mysqli_free_result($req);
    return $result;
}

function get_all_departments()
{
    $sql = "SELECT d.dept_no,
                   d.dept_name,
                   CONCAT(e.first_name, ' ', e.last_name) AS manager_name,
                   (SELECT COUNT(*)
                      FROM dept_emp de
                     WHERE de.dept_no = d.dept_no
                       AND de.to_date = '9999-01-01') AS nb_employees
            FROM departments d
            LEFT JOIN dept_manager dm
                   ON dm.dept_no = d.dept_no
                  AND dm.to_date = '9999-01-01'
            LEFT JOIN employees e
                   ON e.emp_no = dm.emp_no
            ORDER BY d.dept_no";
    return get_all_lines($sql);
}

function get_jobs_stats()
{
    // Statistiques par emploi (titre actuel) :
    // - SUM(e.gender = 'M') compte les lignes où la condition est vraie (1) ou fausse (0) → nb d'hommes
    // - AVG(s.salary) = salaire moyen actuel
    $sql = "SELECT t.title,
                   SUM(e.gender = 'M') AS nb_hommes,
                   SUM(e.gender = 'F') AS nb_femmes,
                   COUNT(*)            AS nb_total,
                   AVG(s.salary)       AS salaire_moyen
            FROM titles t
            INNER JOIN employees e
                    ON e.emp_no = t.emp_no
            INNER JOIN salaries s
                    ON s.emp_no = t.emp_no
                   AND s.to_date = '9999-01-01'
            WHERE t.to_date = '9999-01-01'
            GROUP BY t.title
            ORDER BY t.title";
    return get_all_lines($sql);
}

// Exécute une requête qui ne renvoie pas de résultat (INSERT, UPDATE, DELETE)
function execute_query($sql)
{
    $req = mysqli_query(dbconnect(), $sql);
    if (!$req) {
        die('Erreur SQL : ' . mysqli_error(dbconnect()));
    }
    return $req;
}

// Département actuel de l'employé + sa date de début
function get_current_department($emp_no)
{
    $sql = "SELECT de.dept_no, d.dept_name, de.from_date
            FROM dept_emp de
            INNER JOIN departments d
                    ON d.dept_no = de.dept_no
            WHERE de.emp_no = '%s'
              AND de.to_date = '9999-01-01'";
    $sql = sprintf($sql, $emp_no);
    return get_one_line($sql);
}

// Tous les départements SAUF celui passé en paramètre (pour la liste déroulante)
function get_departments_except($dept_no)
{
    $sql = "SELECT dept_no, dept_name
            FROM departments
            WHERE dept_no <> '%s'
            ORDER BY dept_name";
    $sql = sprintf($sql, $dept_no);
    return get_all_lines($sql);
}

// Change le département courant de l'employé
function change_department($emp_no, $new_dept, $start_date)
{
    // 1) On clôture le département actuel à la date de début du nouveau
    $sql1 = "UPDATE dept_emp
             SET to_date = '%s'
             WHERE emp_no = '%s' AND to_date = '9999-01-01'";
    $sql1 = sprintf($sql1, $start_date, $emp_no);
    execute_query($sql1);

    // 2) On insère le nouveau département comme courant.
    //    ON DUPLICATE KEY UPDATE : si l'employé a déjà appartenu à ce département
    //    (clé primaire emp_no+dept_no existante), on réactive la ligne au lieu de planter.
    $sql2 = "INSERT INTO dept_emp (emp_no, dept_no, from_date, to_date)
             VALUES ('%s', '%s', '%s', '9999-01-01')
             ON DUPLICATE KEY UPDATE from_date = '%s', to_date = '9999-01-01'";
    $sql2 = sprintf($sql2, $emp_no, $new_dept, $start_date, $start_date);
    execute_query($sql2);
}

// Manager actuel d'un département (nom + date de début)
function get_current_manager($dept_no)
{
    $sql = "SELECT dm.emp_no,
                   CONCAT(e.first_name, ' ', e.last_name) AS manager_name,
                   dm.from_date
            FROM dept_manager dm
            INNER JOIN employees e
                    ON e.emp_no = dm.emp_no
            WHERE dm.dept_no = '%s'
              AND dm.to_date = '9999-01-01'";
    $sql = sprintf($sql, $dept_no);
    return get_one_line($sql);
}

// Fait de l'employé le nouveau manager du département
function make_manager($emp_no, $dept_no, $start_date)
{
    // 1) On clôture le mandat du manager actuel à la date de début du nouveau
    $sql1 = "UPDATE dept_manager
             SET to_date = '%s'
             WHERE dept_no = '%s' AND to_date = '9999-01-01'";
    $sql1 = sprintf($sql1, $start_date, $dept_no);
    execute_query($sql1);

    // 2) On insère le nouveau manager comme courant.
    //    ON DUPLICATE KEY UPDATE : si cet employé a déjà managé ce département, on réactive la ligne.
    $sql2 = "INSERT INTO dept_manager (emp_no, dept_no, from_date, to_date)
             VALUES ('%s', '%s', '%s', '9999-01-01')
             ON DUPLICATE KEY UPDATE from_date = '%s', to_date = '9999-01-01'";
    $sql2 = sprintf($sql2, $emp_no, $dept_no, $start_date, $start_date);
    execute_query($sql2);
}

function add_department($dept_no, $dept_name)
{
    $sql = "INSERT INTO departments (dept_no, dept_name)
            VALUES ('%s', '%s')";
    $sql = sprintf($sql, $dept_no, $dept_name);
    execute_query($sql);
}

function update_department($dept_no, $dept_name)
{
    // On ne modifie que le nom : dept_no est la clé primaire, on ne la change pas ici.
    $sql = "UPDATE departments
            SET dept_name = '%s'
            WHERE dept_no = '%s'";
    $sql = sprintf($sql, $dept_name, $dept_no);
    execute_query($sql);
}

function add_employee($emp_no, $birth_date, $first_name, $last_name, $gender, $hire_date)
{
    $sql = "INSERT INTO employees (emp_no, birth_date, first_name, last_name, gender, hire_date)
            VALUES ('%s', '%s', '%s', '%s', '%s', '%s')";
    $sql = sprintf($sql, $emp_no, $birth_date, $first_name, $last_name, $gender, $hire_date);
    execute_query($sql);
}

function update_employee($emp_no, $birth_date, $first_name, $last_name, $gender, $hire_date)
{
    $sql = "UPDATE employees
            SET birth_date = '%s', first_name = '%s', last_name = '%s',
                gender = '%s', hire_date = '%s'
            WHERE emp_no = '%s'";
    $sql = sprintf($sql, $birth_date, $first_name, $last_name, $gender, $hire_date, $emp_no);
    execute_query($sql);
}

// Clôture le mandat du manager actuel d'un département (sans remplaçant)
function remove_manager($dept_no, $end_date)
{
    $sql = "UPDATE dept_manager SET to_date = '%s'
            WHERE dept_no = '%s' AND to_date = '9999-01-01'";
    $sql = sprintf($sql, $end_date, $dept_no);
    execute_query($sql);
}

function get_one_department($dept_no)
{
    // ⚠️ sprintf n'échappe pas : injection SQL toujours possible (à sécuriser avec une requête préparée).
    $sql = "SELECT dept_no, dept_name
            FROM departments
            WHERE dept_no = '%s'";
    $sql = sprintf($sql, $dept_no);
    return get_one_line($sql);
}

function get_employees_by_department($dept_no, $limit, $offset)
{
    // ⚠️ sprintf n'échappe pas : injection SQL toujours possible (à sécuriser avec une requête préparée).
    // %d force des entiers pour LIMIT et OFFSET (pagination).
    $sql = "SELECT e.emp_no,
                   e.first_name,
                   e.last_name,
                   e.gender,
                   e.hire_date
            FROM employees e
            INNER JOIN dept_emp de
                    ON de.emp_no = e.emp_no
            WHERE de.dept_no = '%s'
              AND de.to_date = '9999-01-01'
            ORDER BY e.last_name, e.first_name
            LIMIT %d OFFSET %d";
    $sql = sprintf($sql, $dept_no, $limit, $offset);
    return get_all_lines($sql);
}

function count_employees_by_department($dept_no)
{
    // ⚠️ sprintf n'échappe pas : injection SQL toujours possible (à sécuriser avec une requête préparée).
    $sql = "SELECT COUNT(*) AS total
            FROM dept_emp de
            WHERE de.dept_no = '%s'
              AND de.to_date = '9999-01-01'";
    $sql = sprintf($sql, $dept_no);
    $line = get_one_line($sql);
    return (int)$line['total'];
}

function get_one_employee($emp_no)
{
    // ⚠️ sprintf n'échappe pas : injection SQL toujours possible (à sécuriser avec une requête préparée).
    $sql = "SELECT e.emp_no,
                   e.first_name,
                   e.last_name,
                   e.gender,
                   e.birth_date,
                   e.hire_date,
                   d.dept_no,
                   d.dept_name,
                   t.title,
                   s.salary
            FROM employees e
            LEFT JOIN dept_emp de
                   ON de.emp_no = e.emp_no AND de.to_date = '9999-01-01'
            LEFT JOIN departments d
                   ON d.dept_no = de.dept_no
            LEFT JOIN titles t
                   ON t.emp_no = e.emp_no AND t.to_date = '9999-01-01'
            LEFT JOIN salaries s
                   ON s.emp_no = e.emp_no AND s.to_date = '9999-01-01'
            WHERE e.emp_no = '%s'";
    $sql = sprintf($sql, $emp_no);
    return get_one_line($sql);
}

function get_longest_title($emp_no)
{
    // ⚠️ sprintf n'échappe pas : injection SQL toujours possible (à sécuriser avec une requête préparée).
    // DATEDIFF = nombre de jours entre deux dates.
    // IF(to_date = '9999-01-01', CURDATE(), to_date) : pour un poste encore en cours,
    // on borne la fin à aujourd'hui au lieu de la date sentinelle 9999.
    $sql = "SELECT title,
                   from_date,
                   to_date,
                   DATEDIFF(IF(to_date = '9999-01-01', CURDATE(), to_date), from_date) AS duree_jours
            FROM titles
            WHERE emp_no = '%s'
            ORDER BY duree_jours DESC
            LIMIT 1";
    $sql = sprintf($sql, $emp_no);
    return get_one_line($sql);
}

function get_salary_history($emp_no)
{
    // ⚠️ sprintf n'échappe pas : injection SQL toujours possible (à sécuriser avec une requête préparée).
    $sql = "SELECT salary, from_date, to_date
            FROM salaries
            WHERE emp_no = '%s'
            ORDER BY from_date DESC";
    $sql = sprintf($sql, $emp_no);
    return get_all_lines($sql);
}

function search_employees($dept_no, $name, $age_min, $age_max)
{
    // ⚠️ sprintf n'échappe pas : injection SQL toujours possible (à sécuriser avec une requête préparée).
    // On construit la clause WHERE dynamiquement selon les champs remplis.
    $conditions = array();

    if ($dept_no !== '') {
        $conditions[] = sprintf("de.dept_no = '%s'", $dept_no);
    }
    if ($name !== '') {
        // %% produit un % littéral dans sprintf → '%nom%' pour le LIKE
        $conditions[] = sprintf("(e.first_name LIKE '%%%s%%' OR e.last_name LIKE '%%%s%%')", $name, $name);
    }
    if ($age_min !== '') {
        $conditions[] = sprintf("TIMESTAMPDIFF(YEAR, e.birth_date, CURDATE()) >= %d", $age_min);
    }
    if ($age_max !== '') {
        $conditions[] = sprintf("TIMESTAMPDIFF(YEAR, e.birth_date, CURDATE()) <= %d", $age_max);
    }

    // S'il n'y a aucun filtre, "1=1" garde une clause WHERE valide.
    $where = empty($conditions) ? '1=1' : implode(' AND ', $conditions);

    $sql = "SELECT DISTINCT
                   e.emp_no,
                   e.first_name,
                   e.last_name,
                   e.gender,
                   TIMESTAMPDIFF(YEAR, e.birth_date, CURDATE()) AS age,
                   d.dept_name
            FROM employees e
            INNER JOIN dept_emp de
                    ON de.emp_no = e.emp_no AND de.to_date = '9999-01-01'
            INNER JOIN departments d
                    ON d.dept_no = de.dept_no
            WHERE $where
            ORDER BY e.last_name, e.first_name
            LIMIT 200";
    return get_all_lines($sql);
}

function get_title_history($emp_no)
{
    // ⚠️ sprintf n'échappe pas : injection SQL toujours possible (à sécuriser avec une requête préparée).
    $sql = "SELECT title, from_date, to_date
            FROM titles
            WHERE emp_no = '%s'
            ORDER BY from_date DESC";
    $sql = sprintf($sql, $emp_no);
    return get_all_lines($sql);
}