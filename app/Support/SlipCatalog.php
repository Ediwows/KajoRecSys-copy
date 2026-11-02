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
            'class-admission-slip' => [
                'title' => 'Class Admission Slip',
                'pdf' => 'CLASS-ADMISSION-SLIP.pdf',
                'repeat_y' => [0, 160],
                'prefilled_fields' => [
                    self::field('student_name', 'Name of Student', 36, 68, 152),
                    self::field('sex', 'Sex', 146, 68, 48),
                    self::field('program_year', 'Degree Program & Year Level', 51, 73, 142),
                    self::field('date_absent', 'Date/s Absent/Tardy', 50, 89, 138),
                    self::field('days_absent', 'Number of day/s absent/tardy', 58, 95, 130),
                ],
                'groups' => [[
                    'title' => 'Student Information',
                    'fields' => [
                        self::field('student_id', 'Student ID', 36, 60, 105, true),
                        self::checks('absence_causes', 'Cause/s of Absences', [
                            'health_related' => ['Health-related concerns', 14, 104],
                            'personal_concerns' => ['Personal concerns', 94, 104],
                            'family_responsibilities' => ['Family responsibilities', 174, 104],
                            'socio_cultural' => ['Socio-cultural concern', 14, 109],
                            'environmental' => ['Environmental', 94, 109],
                            'behavioral' => ['Behavioral', 174, 109],
                            'extracurricular' => ['Official co/extra-curricular activity', 14, 114],
                            'other' => ['Others', 94, 114],
                        ]),
                    ],
                ]],
                'guidance_fields' => [
                    self::field('referral_slip_number', 'Reference Referral Slip No.', 90, 79, 55, true),
                    self::field('date_received', 'Date Received', 187, 79, 25, true, 'date'),
                    self::checks('actions_taken', 'Action/s Taken', [
                        'verified_documents' => ['Verified supporting document/s', 14, 128],
                        'interviewed_with_guidance' => ['Interviewed with guidance', 94, 128],
                        'life_coaching' => ['Life coaching', 14, 133],
                        'parent_conference' => ['Parent conference', 94, 133],
                        'other_action' => ['Other', 14, 138],
                    ]),
                    [
                        ...self::field('excused_status', 'Absence Decision', 94, 143, 75, true, 'select'),
                        'options' => [
                            'excused' => 'Excused',
                            'unexcused' => 'Unexcused',
                        ],
                    ],
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
                'pdf' => 'Client-Information-Form.pdf',
                'groups' => [
                    [
                        'title' => 'Personal Information',
                        'fields' => [
                            [...self::field('last_name', 'Last Name', 201, 86, 25, true, 'text', 1, 1), 'readonly' => true],
                            [...self::field('first_name', 'Given Name', 230, 86, 35, true, 'text', 1, 1), 'readonly' => true],
                            [...self::field('middle_name', 'Middle Name', 270, 86, 20, false, 'text', 1, 1), 'readonly' => true],
                            [...self::field('student_id', 'Student ID Number', 292, 86, 25, true, 'text', 1, 1), 'readonly' => true],
                            self::field('contact_number', 'Contact Number', 183, 100, 50, true, 'text', 1, 1),
                            [...self::field('course_year', 'Course and Year', 254, 100, 63, true, 'text', 1, 1), 'readonly' => true],
                            self::field('birth_date', 'Birth Date', 183, 108, 38, false, 'date', 1, 1),
                            [...self::field('sex', 'Sex', 229, 108, 30, false, 'text', 1, 1), 'readonly' => true],
                            self::field('civil_status', 'Civil Status', 264, 108, 45, false, 'text', 1, 1),
                            self::field('nationality', 'Nationality', 183, 116, 45, false, 'text', 1, 1),
                            self::field('birth_place', 'Birth Place', 232, 116, 50, false, 'text', 1, 1),
                            [...self::field('email', 'Email Address', 282, 116, 35, false, 'text', 1, 1), 'readonly' => true],
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
                'pdf_fields' => [
                    ['key' => 'referred_by_name', 'label' => 'Referred by', 'type' => 'text', 'pdf' => ['page' => 1, 'x' => 33, 'y' => 94.5, 'width' => 48], 'auto_fit' => true],
                    ['key' => 'referred_date', 'label' => 'Referral issue date', 'type' => 'date', 'pdf' => ['page' => 1, 'x' => 47, 'y' => 101.5, 'width' => 35]],
                    ['key' => 'received_by_name', 'label' => 'Received by', 'type' => 'text', 'pdf' => ['page' => 1, 'x' => 141, 'y' => 94.5, 'width' => 55], 'auto_fit' => true],
                    ['key' => 'received_date', 'label' => 'Referral received date', 'type' => 'date', 'pdf' => ['page' => 1, 'x' => 155, 'y' => 101.5, 'width' => 35]],
                    ['key' => 'document_code', 'label' => 'Document Code', 'type' => 'text', 'pdf' => ['page' => 1, 'x' => 157, 'y' => 15, 'width' => 24], 'pdf_color' => [220, 0, 0], 'auto_fit' => true],
                    ['key' => 'revision_number', 'label' => 'Revision Number', 'type' => 'text', 'pdf' => ['page' => 1, 'x' => 200, 'y' => 15, 'width' => 10], 'pdf_color' => [220, 0, 0], 'auto_fit' => true],
                    ['key' => 'effectivity_date', 'label' => 'Effectivity Date', 'type' => 'date', 'pdf' => ['page' => 1, 'x' => 157, 'y' => 26, 'width' => 24]],
                    ['key' => 'control_number', 'label' => 'Control Number', 'type' => 'text', 'pdf' => ['page' => 1, 'x' => 190, 'y' => 26, 'width' => 15], 'pdf_color' => [220, 0, 0], 'auto_fit' => true],
                ],
                'groups' => [
                    self::referralStudentGroup(),
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

    /** @return array{title: string, fields: list<array<string, mixed>>} */
    private static function referralStudentGroup(): array
    {
        $group = self::studentGroup([
                        self::field('sex', 'Sex', 149.5, 55.5, 55, true),
                        self::checks('referral_reasons', 'Reason for Referral', [
                            'class_attendance' => ['Class Attendance', 14, 70],
                            'psychological_testing' => ['Psychological Testing', 14, 75],
                            'student_housing' => ['Student Housing (Dormitory)', 14, 80],
                            'academic_deficiencies' => ['Academic Deficiencies', 112, 70],
                            'scholarship_grant_assistance' => ['Scholarship/Grant Assistance', 112, 75],
                            'student_employment' => ['Student Employment (SA/SPS)', 112, 80],
                            'other' => ['Other', 14, 85],
                        ]),
                        self::field('date_absent', 'Date/s Absent/Tardy', 75, 70, 48, true, 'text', 1, 1, false),
                        self::field('days_absent', 'Number of day/s absent/tardy', 0, 0, 1, true, 'number', 1, 1, false),
                        self::field('other_reason', 'Other Reason', 27, 84.5, 182),
                    ], false);

        foreach ($group['fields'] as &$field) {
            if ($field['key'] === 'request_date') {
                $field['pdf']['x'] = 155;
                $field['pdf']['y'] = 47;
                $field['pdf']['width'] = 55;
            } elseif ($field['key'] === 'student_id') {
                $field['pdf']['x'] = 33;
                $field['pdf']['y'] = 48;
                $field['pdf']['width'] = 115;
            } elseif ($field['key'] === 'student_name') {
                $field['pdf']['x'] = 37;
                $field['pdf']['y'] = 51.5;
                $field['pdf']['width'] = 164;
            } elseif ($field['key'] === 'program_year') {
                $field['pdf']['x'] = 52.5;
                $field['pdf']['y'] = 55.5;
                $field['pdf']['width'] = 145;
            }
        }
        unset($field);

        return $group;
    }

    /**
     * @return array<string, array{title: string, pdf: string, repeat_y?: list<float>, groups: list<array{title: string, fields: list<array<string, mixed>>}>}>
     */
    public static function forRole(string $role): array
    {
        $slips = match ($role) {
            'teacher' => ['student-referral-slip'],
            'guidance_counselor' => [
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
    private static function studentGroup(array $extra = [], bool $includeCollege = true, float $studentInfoYOffset = 0): array
    {
        return [
            'title' => 'Student Information',
            'fields' => [
                self::field('student_id', 'Student ID Number', 40, 54 + $studentInfoYOffset, 105, true),
                self::field('request_date', 'Date', 150, 54 + $studentInfoYOffset, 45, false, 'date'),
                self::field('student_name', 'Name of Student', 43, 59 + $studentInfoYOffset, 152, true),
                self::field('program_year', 'Degree Program and Year Level', 60, 64 + $studentInfoYOffset, 135, true),
                ...($includeCollege ? [self::field('college', 'College', 33, 69 + $studentInfoYOffset, 162)] : []),
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
        bool $includePdf = true,
    ): array {
        $field = [
            'key' => $key,
            'label' => $label,
            'type' => $type,
            'required' => $required,
            'rows' => $rows,
            'pdf' => $includePdf ? ['page' => $page, 'x' => $x, 'y' => $y, 'width' => $width] : null,
        ];

        return $field;
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
