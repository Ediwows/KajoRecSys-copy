<?php

namespace App\Support;

class SlipCatalog
{
    /**
     * @return array<string, array{title: string, pdf: string, repeat_y?: list<float>, groups: list<array{title: string, fields: list<array<string, mixed>>}>}>
     */
    public static function all(): array
    {
        return [
            'case-referral-slip' => [
                'title' => 'Case Referral Slip',
                'pdf' => 'CASE-REFERRAL-SLIP.pdf',
                'groups' => [[
                    'title' => 'Referral Information',
                    'fields' => [
                        self::field('recipient', 'Refer to (office or person)', 40, 115, 150, true),
                        self::field('student_name', 'Student Name', 90, 135, 105, true),
                        self::field('program', 'Program / Degree and Year', 20, 145, 160, true),
                        self::field('observations', 'Initial Observations', 20, 176, 175, true, 'textarea', 5),
                    ],
                ]],
            ],
            'class-admission-slip' => [
                'title' => 'Class Admission Slip',
                'pdf' => 'CLASS-ADMISSION-SLIP.pdf',
                'repeat_y' => [0, 160],
                'groups' => [
                    self::studentGroup([
                        self::field('sex', 'Sex', 125, 82, 65),
                        self::field('referral_slip', 'Reference Referral Slip No.', 20, 89, 95),
                        self::field('date_absent', 'Date / Time Absent or Tardy', 68, 97, 127, true),
                        self::field('days_absent', 'Number of Days Absent / Tardy', 82, 104, 113, true),
                        self::field('absence_causes', "Student's Reason / Supporting Proof (Excused or Not)", 20, 124, 175, true, 'textarea', 3),
                    ]),
                ],
            ],
            'feedback-form' => [
                'title' => 'Feedback Form',
                'pdf' => 'FEEDBACK-FORM.pdf',
                'repeat_y' => [0, 160],
                'groups' => [
                    self::studentGroup([
                        self::field('sex', 'Sex', 130, 83, 60),
                        self::field('referral_slip', 'Reference Referral Slip No.', 20, 91, 90),
                        self::field('reason', 'Reason for Referral', 20, 112, 175, true, 'textarea', 3),
                    ], false),
                ],
            ],
            'qf-bo-oss-04-client-information-form' => [
                'title' => 'Client Information Form',
                'pdf' => 'QF-BO-OSS-04-Client-Information-Form.pdf',
                'groups' => [
                    [
                        'title' => 'Personal Information',
                        'fields' => [
                            self::field('last_name', 'Last Name', 183, 91, 42, true, 'text', 1, 1),
                            self::field('first_name', 'Given Name', 230, 91, 38, true, 'text', 1, 1),
                            self::field('middle_name', 'Middle Name', 272, 91, 45, false, 'text', 1, 1),
                            self::field('student_id', 'Student ID Number', 272, 83, 45, true, 'text', 1, 1),
                            self::field('contact_number', 'Contact Number', 183, 100, 50, true, 'text', 1, 1),
                            self::field('course_year', 'Course and Year', 254, 100, 63, true, 'text', 1, 1),
                            self::field('birth_date', 'Birth Date', 183, 108, 38, false, 'date', 1, 1),
                            self::field('sex', 'Sex', 229, 108, 30, false, 'text', 1, 1),
                            self::field('civil_status', 'Civil Status', 264, 108, 45, false, 'text', 1, 1),
                            self::field('nationality', 'Nationality', 183, 116, 45, false, 'text', 1, 1),
                            self::field('birth_place', 'Birth Place', 232, 116, 50, false, 'text', 1, 1),
                            self::field('email', 'Email Address', 282, 116, 35, false, 'text', 1, 1),
                            self::field('languages', 'Languages / Dialects', 183, 126, 133, false, 'text', 1, 1),
                            self::field('address', 'Address while Studying in BSU', 183, 137, 133, true, 'textarea', 2, 1),
                            self::field('disability', 'Disability (if any)', 202, 161, 110, false, 'text', 1, 1),
                            self::field('indigenous_group', 'Indigenous Group (if any)', 202, 169, 110, false, 'text', 1, 1),
                            self::field('pwd', 'Person with Disability', 222, 177, 10, false, 'text', 1, 1),
                            self::field('student_parent', 'Student Parent', 268, 177, 10, false, 'text', 1, 1),
                            self::field('financial_sources', 'Sources of Financial Support', 183, 190, 133, false, 'textarea', 2, 1),
                        ],
                    ],
                    [
                        'title' => 'Family and Educational Background',
                        'fields' => [
                            self::field('parent_marital_status', 'Parents’ Marital Status', 15, 22, 92, false, 'text', 1, 2),
                            self::field('father_name', 'Father’s Name', 16, 74, 50, false, 'text', 1, 2),
                            self::field('mother_name', 'Mother’s Name', 67, 74, 50, false, 'text', 1, 2),
                            self::field('guardian_name', 'Legal Guardian’s Name', 119, 74, 51, false, 'text', 1, 2),
                            self::field('father_age', 'Father’s Age', 16, 84, 49, false, 'text', 1, 2),
                            self::field('mother_age', 'Mother’s Age', 67, 84, 50, false, 'text', 1, 2),
                            self::field('guardian_relationship', 'Guardian Relationship', 119, 84, 51, false, 'text', 1, 2),
                            self::field('father_occupation', 'Father’s Occupation', 16, 94, 49, false, 'text', 1, 2),
                            self::field('mother_occupation', 'Mother’s Occupation', 67, 94, 50, false, 'text', 1, 2),
                            self::field('guardian_occupation', 'Guardian Occupation', 119, 94, 51, false, 'text', 1, 2),
                            self::field('father_contact', 'Father’s Contact Number', 16, 123, 49, false, 'text', 1, 2),
                            self::field('mother_contact', 'Mother’s Contact Number', 67, 123, 50, false, 'text', 1, 2),
                            self::field('guardian_contact', 'Guardian Contact Number', 119, 123, 51, false, 'text', 1, 2),
                            self::field('siblings', 'Siblings (name, age, education, civil status, occupation)', 15, 143, 155, false, 'textarea', 7, 2),
                            self::field('educational_background', 'Educational Background (level, school, year, awards)', 172, 71, 145, false, 'textarea', 5, 2),
                            self::field('about_me', 'More About Me', 172, 109, 145, false, 'textarea', 10, 2),
                        ],
                    ],
                    [
                        'title' => 'Consent Information',
                        'fields' => [
                            self::checks('consent', 'Informed Consent', [
                                'agree' => ['I have read and agree to the informed consent statement', 15, 104],
                            ], true, 3),
                            self::field('consent_name', 'Printed Name for Consent', 12, 103, 125, true, 'text', 1, 3),
                        ],
                    ],
                ],
            ],
            'qf-bo-oss-19-scholars-accomplishment-report' => [
                'title' => 'Scholarship Grantee Accomplishment Report',
                'pdf' => 'QF-BO-OSS-19-Scholars-Accomplishment-Report.pdf',
                'groups' => [[
                    'title' => 'Scholarship Accomplishments',
                    'fields' => [
                        self::field('student_id', 'ID Number', 30, 39, 60, true),
                        self::field('student_name', 'Name', 67, 39, 178, true),
                        self::field('course_year', 'Course and Year', 276, 39, 44, true),
                        self::field('activity_sponsor_1', 'Activity 1 Sponsor / Agency', 8, 58, 55),
                        self::field('activity_title_1', 'Activity 1 Title', 67, 58, 60),
                        self::field('activity_date_1', 'Activity 1 Date', 128, 58, 58),
                        self::field('activity_venue_1', 'Activity 1 Venue', 190, 58, 42),
                        self::field('activity_verification_1', 'Activity 1 Verification', 235, 58, 87),
                        self::field('activity_sponsor_2', 'Activity 2 Sponsor / Agency', 8, 66, 55),
                        self::field('activity_title_2', 'Activity 2 Title', 67, 66, 60),
                        self::field('activity_date_2', 'Activity 2 Date', 128, 66, 58),
                        self::field('activity_venue_2', 'Activity 2 Venue', 190, 66, 42),
                        self::field('activity_verification_2', 'Activity 2 Verification', 235, 66, 87),
                        self::field('activity_sponsor_3', 'Activity 3 Sponsor / Agency', 8, 74, 55),
                        self::field('activity_title_3', 'Activity 3 Title', 67, 74, 60),
                        self::field('activity_date_3', 'Activity 3 Date', 128, 74, 58),
                        self::field('activity_venue_3', 'Activity 3 Venue', 190, 74, 42),
                        self::field('activity_verification_3', 'Activity 3 Verification', 235, 74, 87),
                        self::field('fruit_tree_type', 'Type of Fruit Trees', 8, 105, 130),
                        self::field('date_planted', 'Date Planted', 150, 105, 55, false, 'date'),
                        self::field('plant_area', 'Area / Location', 213, 105, 110),
                        self::field('quarter_3_report', 'Third Quarter Narrative', 8, 125, 74, false, 'textarea', 3),
                        self::field('quarter_4_report', 'Fourth Quarter Narrative', 88, 125, 74, false, 'textarea', 3),
                        self::field('quarter_1_report', 'First Quarter Narrative', 168, 125, 74, false, 'textarea', 3),
                        self::field('quarter_2_report', 'Second Quarter Narrative', 248, 125, 74, false, 'textarea', 3),
                        self::field('verification_mode', 'Mode of Verification', 8, 151, 315, false, 'textarea', 2),
                    ],
                ]],
            ],
            'student-leaveof-absence-slip' => [
                'title' => 'Student Leave of Absence Slip',
                'pdf' => 'STUDENT-LEAVEOF-ABSENCE-SLIP.pdf',
                'repeat_y' => [0, 160],
                'groups' => [
                    self::studentGroup([
                        self::field('leave_dates', 'Inclusive Dates of Leave', 20, 96, 175, true),
                        self::field('return_semester', 'Semester and School Year Intended to Return', 20, 103, 175, true),
                        self::field('leave_reason', 'Reason for Stopping / Filing a Leave', 20, 113, 175, true, 'textarea', 3),
                        self::field('leave_plan', 'What to Do While on Leave', 20, 133, 175, false, 'textarea', 3),
                    ]),
                ],
            ],
            'student-readmission-form' => [
                'title' => 'Student Readmission Slip',
                'pdf' => 'STUDENT-READMISSION-FORM.pdf',
                'repeat_y' => [0, 160],
                'groups' => [
                    self::studentGroup([
                        self::field('departure_date', 'Date of Approved Leave', 75, 99, 100, false, 'date'),
                        self::field('return_date', 'Inclusive Period of Leave', 75, 106, 100, true),
                        self::field('leave_reason', 'Reason for Leave', 75, 113, 100, true),
                        self::field('date_return', 'Date of Return', 75, 123, 100, false, 'date'),
                        self::field('semester_year', 'Semester / Academic Year for Readmission', 75, 130, 100, true),
                    ]),
                ],
            ],
            'student-referral-slip' => [
                'title' => 'Student Referral Slip',
                'pdf' => 'STUDENT-REFERRAL-SLIP.pdf',
                'repeat_y' => [0, 160],
                'groups' => [
                    self::studentGroup([
                        self::field('sex', 'Sex', 145, 76, 48),
                        self::checks('referral_reasons', 'Reason for Referral', [
                            'class_attendance' => ['Class Attendance', 12, 102],
                            'psychological_testing' => ['Psychological Testing', 12, 107],
                            'student_housing' => ['Student Housing', 12, 112],
                            'academic_deficiencies' => ['Academic Deficiencies', 145, 102],
                            'scholarship_grant_assistance' => ['Scholarship / Grant Assistance', 145, 107],
                            'student_employment' => ['Student Employment', 145, 112],
                        ]),
                        self::field('other_reason', 'Other Reason', 24, 119, 170),
                    ], false),
                ],
            ],
            'student-withdrawal-dropping-shifting-counseling-slip' => [
                'title' => 'Student Withdrawal / Dropping / Shifting Counseling Slip',
                'pdf' => 'STUDENT-WITHDRAWAL-DROPPING-SHIFTING-COUNSELING-SLIP.pdf',
                'repeat_y' => [0, 165],
                'groups' => [
                    self::studentGroup([
                        self::checks('request_type', 'Request', [
                            'withdrawal' => ['Withdrawal', 24, 82],
                            'dropping' => ['Dropping', 64, 82],
                            'shifting' => ['Shifting Program', 126, 82],
                        ]),
                        self::field('request_reason', 'Reason(s) for Request', 54, 91, 141, true, 'textarea', 3),
                    ]),
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{title: string, pdf: string, repeat_y?: list<float>, groups: list<array{title: string, fields: list<array<string, mixed>>}>}>
     */
    public static function forRole(string $role): array
    {
        $slips = match ($role) {
            'teacher' => ['student-referral-slip'],
            'guidance_counselor' => [
                'qf-bo-oss-04-client-information-form',
                'class-admission-slip',
            ],
            default => [],
        };

        return array_intersect_key(self::all(), array_flip($slips));
    }

    /**
     * @return array<string, array{title: string, pdf: string, repeat_y?: list<float>, groups: list<array{title: string, fields: list<array<string, mixed>>}>}>
     */
    public static function studentRequestForms(): array
    {
        $slips = self::all();

        return array_intersect_key($slips, array_flip([
            'student-leaveof-absence-slip',
            'student-withdrawal-dropping-shifting-counseling-slip',
            'student-readmission-form',
        ]));
    }

    /**
     * @return list<string>
     */
    public static function workflowSlugs(): array
    {
        return array_values(array_unique([
            ...array_keys(self::forRole('teacher')),
            ...array_keys(self::forRole('guidance_counselor')),
            ...array_keys(self::studentRequestForms()),
        ]));
    }

    /**
     * @param  list<array<string, mixed>>  $extra
     * @return array{title: string, fields: list<array<string, mixed>>}
     */
    private static function studentGroup(array $extra = [], bool $includeCollege = true): array
    {
        return [
            'title' => 'Student Information',
            'fields' => [
                self::field('student_id', 'Student ID Number', 40, 54, 105, true),
                self::field('request_date', 'Date', 150, 54, 45, false, 'date'),
                self::field('student_name', 'Name of Student', 43, 59, 152, true),
                self::field('program_year', 'Degree Program and Year Level', 75, 64, 120, true),
                ...($includeCollege ? [self::field('college', 'College', 33, 69, 162)] : []),
                ...$extra,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function field(
        string $key,
        string $label,
        float $x,
        float $y,
        float $width,
        bool $required = false,
        string $type = 'text',
        int $rows = 1,
        int $page = 1,
    ): array {
        return [
            'key' => $key,
            'label' => $label,
            'type' => $type,
            'required' => $required,
            'rows' => $rows,
            'pdf' => ['page' => $page, 'x' => $x, 'y' => $y, 'width' => $width],
        ];
    }

    /**
     * @param  array<string, array{0: string, 1: float, 2: float}>  $options
     * @return array<string, mixed>
     */
    private static function checks(string $key, string $label, array $options, bool $required = true, int $page = 1): array
    {
        $pdfOptions = [];
        $formOptions = [];

        foreach ($options as $value => [$optionLabel, $x, $y]) {
            $formOptions[$value] = $optionLabel;
            $pdfOptions[$value] = ['page' => $page, 'x' => $x, 'y' => $y];
        }

        return [
            'key' => $key,
            'label' => $label,
            'type' => 'checkboxes',
            'required' => $required,
            'options' => $formOptions,
            'pdf_options' => $pdfOptions,
        ];
    }
}
