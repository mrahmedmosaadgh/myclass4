export const menuGroups = [
    {
        title: 'Admin',
        role: 'admin',
        items: [
            {
                name: 'Schools',
                route: 'admin.school.index',
                permission: 'admin_manage_school'
            },
            {
                name: 'Academic Years',
                route: 'admin.academic-year.index',
                permission: 'admin_manage_academic_year'
            },
            {
                name: 'Semesters',
                route: 'admin.semester.index',
                permission: 'admin_manage_semester'
            }
        ]
    },
    {
        title: 'Users Management',
        role: 'admin',
        items: [
            {
                name: 'Teachers',
                route: 'admin.teacher.index',
                permission: 'admin_manage_teacher'
            },
            {
                name: 'Students',
                route: 'admin.students.index',
                permission: 'admin_manage_student'
            }
        ]
    },
    {
        title: 'Schedule',
        role: 'admin',
        items: [
            {
                name: 'Classrooms',
                route: 'admin.classroom.index',
                permission: 'admin_manage_classroom'
            },
            {
                name: 'Schedules',
                route: 'admin.schedules.index',
                permission: 'admin_manage_schedule'
            },
            {
                name: 'schedule-copies',
                route: 'admin.schedule-copies.index',
                permission: 'admin_manage_schedule'
            },


            {
                name: 'Calendar',
                route: 'admin.calendar.index',
                permission: 'admin_manage_calendar'
            }
        ]
    },
    {
        title: 'Teacher',
        role: 'teacher',
        items: [
            {
                name: 'Dashboard',
                route: 'teacher.home',
                icon: 'HomeIcon'
            },
            {
                name: 'My Classes',
                route: 'teacher.classes',
                icon: 'AcademicCapIcon'
            },
            {
                name: 'Attendance',
                route: 'teacher.attendance',
                icon: 'ClipboardIcon'
            },
            {
                name: 'Grades',
                route: 'teacher.grades',
                icon: 'ChartBarIcon'
            }
        ]
    },
    {
        title: 'Student',
        role: 'student',
        items: [
            {
                name: 'Dashboard',
                route: 'student.home',
                icon: 'HomeIcon'
            },
            {
                name: 'My Schedule',
                route: 'student.schedule',
                icon: 'CalendarIcon'
            },
            {
                name: 'My Grades',
                route: 'student.grades',
                icon: 'ChartBarIcon'
            },
            {
                name: 'Attendance Record',
                route: 'student.attendance',
                icon: 'ClipboardCheckIcon'
            }
        ]
    }
];
