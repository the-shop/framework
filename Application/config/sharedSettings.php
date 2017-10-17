<?php

return [
    'roles' => [
        'admin' => 'Admin',
        'standard' => 'Standard',
        'accountant' => 'Accountant',
    ],
    'hourlyRate' => 500,
    'employees' => [
        'roles' => [
            'Apprentice' => [
                'minimumEarnings' => 10000,
                'coefficient' => 0.27,
                'xpEntryPoint' => 200,
            ],
            'Junior' => [
                'minimumEarnings' => 17500,
                'coefficient' => 0.26,
                'xpEntryPoint' => 400,
            ],
            'Standard' => [
                'minimumEarnings' => 30000,
                'coefficient' => 0.26,
                'xpEntryPoint' => 600,
            ],
            'Senior' => [
                'minimumEarnings' => 45000,
                'coefficient' => 0.25,
                'xpEntryPoint' => 800,
            ],
            'Leader' => [
                'minimumEarnings' => 60000,
                'coefficient' => 0.25,
                'xpEntryPoint' => 1000,
            ],
        ],
    ],
    'projects' => [
        'reservation' => [
            'maxReservationTime' => getenv('PROJECT_RESERVATION_TIME'),
        ],
    ],
    'tasks' => [
        'reservation' => [
            'maxReservationTime' => getenv('PROJECT_TASK_RESERVATION_TIME'),
        ],
    ],
    'taskHistoryStatuses' => [
        'assigned' => 'Task assigned to %s',
        'claimed' => 'Task claimed by %s',
        'paused' => 'Task paused because of: "%s"',
        'resumed' => 'Task resumed',
        'qa_ready' => 'Task ready for QA',
        'qa_fail' => 'Task failed QA',
        'qa_success' => 'Task passed QA',
        'blocked' => 'Task is currently : %s',
        'qa_in_progress' => 'Task is in QA progress',
    ],
    'taskComplexityOptions' => [
        1,
        2,
        3,
        4,
        5,
        6,
        7,
        8,
        9,
        10,
    ],
    'guestRole' => 'guest',
    'defaultRole' => 'standard',
    'skills' => [
        'PHP',
        'React',
        'DevOps',
        'Node',
        'Planning',
        'Management',
    ],
    'employeeMonthlyMinimum' => [
        'apprentice' => 10000,
        'junior' => 17500,
        'standard' => 30000,
        'senior' => 45000,
    ],
    'taskPriorities' => [
        'High',
        'Medium',
        'Low',
    ],
];
