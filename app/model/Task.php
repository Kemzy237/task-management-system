<?php
function insert_task($conn, $data){
    $sql = "INSERT INTO tasks (title, description, assigned_to, due_date) VALUES(?,?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute($data);
}

function get_all_tasks($conn){
    $sql = "SELECT * FROM tasks ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([]);
    if($stmt->rowCount() > 0){
        $tasks = $stmt->fetchAll();
    }else $tasks = 0;
    return $tasks;
}

function get_all_tasks_due_today($conn){
    $sql = "SELECT * FROM tasks WHERE due_date = CURDATE() AND status != 'completed' ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([]);
    if($stmt->rowCount() > 0){
        $tasks = $stmt->fetchAll();
    }else $tasks = 0;
    return $tasks;
}

function count_tasks_due_today($conn){
    $sql = "SELECT id FROM tasks WHERE due_date = CURDATE() AND status != 'completed'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([]);
    return $stmt->rowCount();
}

function get_all_tasks_overdue($conn){
    $sql = "SELECT * FROM tasks WHERE due_date < CURDATE() AND (due_date IS NULL OR due_date != '0000-00-00') AND status !='completed' ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([]);
    if($stmt->rowCount() > 0){
        $tasks = $stmt->fetchAll();
    }else $tasks = 0;
    return $tasks;
}

function count_tasks_overdue($conn){
    $sql = "SELECT id FROM tasks WHERE due_date < CURDATE() AND (due_date IS NULL OR due_date != '0000-00-00') AND status !='completed'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([]);
    return $stmt->rowCount();
}

function get_all_tasks_noDeadline($conn){
    $sql = "SELECT * FROM tasks WHERE (due_date IS NULL OR due_date = '0000-00-00') AND status != 'completed' ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([]);
    if($stmt->rowCount() > 0){
        $tasks = $stmt->fetchAll();
    }else $tasks = 0;
    return $tasks;
}

function count_tasks_noDeadline($conn){
    $sql = "SELECT id FROM tasks WHERE (due_date IS NULL OR due_date = '0000-00-00') AND status != 'completed'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([]);
    return $stmt->rowCount();
}

function get_all_tasks_pending($conn){
    $sql = "SELECT * FROM tasks WHERE status = 'pending' AND due_date >= CURDATE() OR (due_date IS NULL OR due_date = '0000-00-00') AND status != 'in_progress' ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([]);
    if($stmt->rowCount() > 0){
        $tasks = $stmt->fetchAll();
    }else $tasks = 0;
    return $tasks;
}

function count_tasks_pending($conn){
    $sql = "SELECT id FROM tasks WHERE status = 'pending' AND due_date >= CURDATE() OR (due_date IS NULL OR due_date = '0000-00-00') AND status != 'in_progress'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([]);
    return $stmt->rowCount();
}

function get_all_tasks_completed($conn){
    $sql = "SELECT * FROM tasks WHERE status = 'completed' ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([]);
    if($stmt->rowCount() > 0){
        $tasks = $stmt->fetchAll();
    }else $tasks = 0;
    return $tasks;
}

function count_completed($conn){
    $sql = "SELECT id FROM tasks WHERE status = 'completed'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([]);
    return $stmt->rowCount();
}

function get_all_tasks_in_progress($conn){
    $sql = "SELECT * FROM tasks WHERE status = 'in_progress' AND due_date >= CURDATE() OR (due_date IS NULL OR due_date = '0000-00-00') ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([]);
    if($stmt->rowCount() > 0){
        $tasks = $stmt->fetchAll();
    }else $tasks = 0;
    return $tasks;
}

function count_tasks_in_progress($conn){
    $sql = "SELECT id FROM tasks WHERE status = 'in_progress' AND due_date >= CURDATE() OR (due_date IS NULL OR due_date = '0000-00-00')";
    $stmt = $conn->prepare($sql);
    $stmt->execute([]);
    return $stmt->rowCount();
}

function get_all_tasks_due_in_7_days($conn){
    $sql = "SELECT * FROM tasks WHERE due_date BETWEEN CURDATE() AND CURDATE() + INTERVAL 7 DAY;";
    $stmt = $conn->prepare($sql);
    $stmt->execute([]);
    if($stmt->rowCount() > 0){
        $tasks = $stmt->fetchAll();
    }else $tasks = 0;
    return $tasks;
}

function count_tasks_due_in_7_days($conn){
    $sql = "SELECT id FROM tasks WHERE due_date BETWEEN CURDATE() AND CURDATE() + INTERVAL 7 DAY;";
    $stmt = $conn->prepare($sql);
    $stmt->execute([]);
    return $stmt->rowCount();
}

function get_all_tasks_due_in_30_days($conn){
    $sql = "SELECT * FROM tasks WHERE due_date BETWEEN CURDATE() AND CURDATE() + INTERVAL 30 DAY;";
    $stmt = $conn->prepare($sql);
    $stmt->execute([]);
    if($stmt->rowCount() > 0){
        $tasks = $stmt->fetchAll();
    }else $tasks = 0;
    return $tasks;
}

function count_tasks_due_in_30_days($conn){
    $sql = "SELECT id FROM tasks WHERE due_date BETWEEN CURDATE() AND CURDATE() + INTERVAL 30 DAY;";
    $stmt = $conn->prepare($sql);
    $stmt->execute([]);
    return $stmt->rowCount();
}

function delete_task($conn, $data){
    $sql = "DELETE FROM tasks WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->execute($data);
}

function get_task_by_id($conn, $id){
    $sql = "SELECT * FROM tasks WHERE id =?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    if($stmt->rowCount() > 0){
        $task = $stmt->fetch();
    }else $task = 0;
    return $task;
}

function count_tasks($conn){
    $sql = "SELECT id FROM tasks";
    $stmt = $conn->prepare($sql);
    $stmt->execute([]);
    return $stmt->rowCount();
}

function update_task($conn, $data){
    $sql = "UPDATE tasks SET title=?, description=?, assigned_to=?, due_date=?, status=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->execute($data);
}

function get_all_tasks_by_id($conn, $id){
    $sql = "SELECT * FROM tasks WHERE assigned_to=? ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    if($stmt->rowCount() > 0){
        $tasks = $stmt->fetchAll();
    }else $tasks = 0;
    return $tasks;
}

function update_task_status($conn, $data){
    $sql = "UPDATE tasks SET status=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->execute($data);
}

function count_my_task($conn, $id){
    $sql = "SELECT id FROM tasks WHERE assigned_to=?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->rowCount();
}

function count_overdue_task($conn, $id){
    $sql = "SELECT id FROM tasks WHERE assigned_to = ? AND due_date < CURDATE() AND (due_date IS NULL OR due_date != '0000-00-00') AND status !='completed'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->rowCount();
}

function count_my_tasks_noDeadline($conn, $id){
    $sql = "SELECT id FROM tasks WHERE (due_date IS NULL OR due_date = '0000-00-00') AND assigned_to = ? AND status != 'completed'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->rowCount();
}

function count_my_completed($conn, $id){
    $sql = "SELECT id FROM tasks WHERE status = 'completed' AND assigned_to = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->rowCount();
}

function count_my_tasks_due_today($conn, $id){
    $sql = "SELECT id FROM tasks WHERE due_date = CURDATE() AND assigned_to = ? AND status != 'completed'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->rowCount();
}

function count_my_tasks_pending($conn, $id){
    $sql = "SELECT id FROM tasks WHERE status = 'pending' AND due_date > CURDATE() AND assigned_to = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->rowCount();
}

function count_my_tasks_in_progress($conn, $id){
    $sql = "SELECT id FROM tasks WHERE status = 'in_progress' AND due_date > CURDATE() AND assigned_to = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->rowCount();
}

function get_all_my_tasks_due_today($conn, $id){
    $sql = "SELECT * FROM tasks WHERE due_date = CURDATE() AND assigned_to = ? AND status != 'completed' ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    if($stmt->rowCount() > 0){
        $tasks = $stmt->fetchAll();
    }else $tasks = 0;
    return $tasks;
}

function get_all_my_tasks_overdue($conn, $id){
    $sql = "SELECT * FROM tasks WHERE due_date < CURDATE() AND (due_date IS NULL OR due_date != '0000-00-00')  AND assigned_to = ? AND status !='completed' ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    if($stmt->rowCount() > 0){
        $tasks = $stmt->fetchAll();
    }else $tasks = 0;
    return $tasks;
}

function get_all_my_tasks_completed($conn, $id){
    $sql = "SELECT * FROM tasks WHERE status = 'completed' AND assigned_to = ? ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    if($stmt->rowCount() > 0){
        $tasks = $stmt->fetchAll();
    }else $tasks = 0;
    return $tasks;
}

function get_all_my_tasks_pending($conn, $id){
    $sql = "SELECT * FROM tasks WHERE (status = 'pending' AND due_date >= CURDATE()) AND assigned_to = ? ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    if($stmt->rowCount() > 0){
        $tasks = $stmt->fetchAll();
    }else $tasks = 0;
    return $tasks;
}

function get_all_my_tasks_in_progress($conn, $id){
    $sql = "SELECT * FROM tasks WHERE (status = 'in_progress' AND due_date >= CURDATE()) AND assigned_to = ? ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    if($stmt->rowCount() > 0){
        $tasks = $stmt->fetchAll();
    }else $tasks = 0;
    return $tasks;
}

function get_all_my_tasks_noDeadline($conn, $id){
    $sql = "SELECT * FROM tasks WHERE (due_date IS NULL OR due_date = '0000-00-00') AND assigned_to = ? AND status != 'completed' ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    if($stmt->rowCount() > 0){
        $tasks = $stmt->fetchAll();
    }else $tasks = 0;
    return $tasks;
}

// function overdue($conn, $id){
//     $sql = "SELECT * FROM tasks WHERE id = ? AND due_date < CURDATE()";
//     $stmt = $conn->prepare($sql);
//     $stmt->execute([$id]);
//     if($stmt->rowCount() > 0){
//         $overdue = 1;
//     }else{
//         $overdue = 0;
//     }
//     return $overdue;
// }

// function overdue($conn, $id) {
//     $sql = "SELECT COUNT(*) AS overdue_count FROM tasks WHERE id = ? AND due_date > CURDATE()";

//     $stmt = $conn->prepare($sql);
//     $stmt->execute([$id]);

//     $result = $stmt->fetch(PDO::FETCH_ASSOC);
//     return $result['overdue_count'] > 0;
// }