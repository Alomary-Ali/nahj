<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionOption;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Database\Seeder;

class CurriculumSeeder extends Seeder
{
    public function run(): void
    {
        $instructor = User::where('email', 'instructor@coursura.com')->first();
        if (! $instructor) {
            return;
        }

        // 1. Categories
        $aiCategory = Category::firstOrCreate(
            ['slug' => 'ai-data-science'],
            [
                'name' => 'الذكاء الاصطناعي وعلم البيانات (AI & Data Science)',
                'description' => 'تعلّم الآلة، الذكاء الاصطناعي التوليدي، وتحليل البيانات الضخمة بأحدث النماذج العالمية.',
                'icon' => 'cpu-chip',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $csCategory = Category::firstOrCreate(
            ['slug' => 'computer-science'],
            [
                'name' => 'علوم الحاسوب وهندسة البرمجيات (Computer Science)',
                'description' => 'الخوارزميات، هياكل البيانات، تطوير الويب المتكامل، وهندسة الأنظمة السحابية.',
                'icon' => 'code-bracket',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        $uxCategory = Category::firstOrCreate(
            ['slug' => 'design-ui-ux'],
            [
                'name' => 'تصميم الواجهات وتجربة المستخدم (UI/UX Design)',
                'description' => 'أبحاث المستخدمين، بناء النماذج الأولية Wireframes، ونظم التصميم الاحترافية.',
                'icon' => 'paint-brush',
                'is_active' => true,
                'sort_order' => 3,
            ]
        );

        // 2. Specialization
        $spec = Specialization::firstOrCreate(
            ['slug' => 'deep-learning-specialization'],
            [
                'title' => 'تخصص التعلم العميق والشبكات العصبية (Deep Learning Specialization)',
                'headline' => 'كن خبيراً في هندسة نماذج الذكاء الاصطناعي الحديثة مع نخبة المدربين العالميين.',
                'description' => 'هذا التخصص يأخذك في رحلة متكاملة من أساسيات الشبكات العصبية حتى نماذج المحولات Transformers المتقدمة.',
                'instructor_id' => $instructor->id,
                'category_id' => $aiCategory->id,
                'level' => 'intermediate',
                'is_published' => true,
            ]
        );

        // 3. Flagship Course
        $course = Course::firstOrCreate(
            ['slug' => 'neural-networks-and-deep-learning'],
            [
                'title' => 'الشبكات العصبية والتعلم العميق (Neural Networks and Deep Learning)',
                'headline' => 'المقرر الأول في مسار الذكاء الاصطناعي العالمي، يغطي بناء وتدريب الشبكات العصبية من الصفر.',
                'description' => 'ستتعلم في هذا المساق المعماريات الأساسية للتعلم العميق، وكيفية بناء الشبكات العصبية الرياضية وتطبيقها بلغة بايثون بدون مكتبات خارجية، ثم التوسع للنماذج المعقدة.',
                'instructor_id' => $instructor->id,
                'category_id' => $aiCategory->id,
                'specialization_id' => $spec->id,
                'level' => 'intermediate',
                'language' => 'ar',
                'price' => 49.00,
                'duration_hours' => 24,
                'is_published' => true,
            ]
        );

        // 4. Modules (Weeks)
        $module1 = CourseModule::firstOrCreate(
            ['course_id' => $course->id, 'sort_order' => 1],
            [
                'title' => 'الأسبوع 1: مقدمة إلى التعلم العميق والرياضيات الأساسية',
                'description' => 'نظرة عامة على ثورة الذكاء الاصطناعي، ولماذا تتفوق الشبكات العصبية اليوم.',
            ]
        );

        $module2 = CourseModule::firstOrCreate(
            ['course_id' => $course->id, 'sort_order' => 2],
            [
                'title' => 'الأسبوع 2: الانحدار اللوجستي كشبكة عصبية مبسطة',
                'description' => 'الانتشار الأمامي Forward Propagation والانتشار العكسي Backpropagation وحساب التفاضل والتكامل.',
            ]
        );

        // 5. Lessons for Module 1
        Lesson::firstOrCreate(
            ['module_id' => $module1->id, 'sort_order' => 1],
            [
                'title' => 'مرحباً بك في عالم التعلم العميق (Course Overview)',
                'slug' => 'welcome-course-overview',
                'type' => 'video',
                'duration_seconds' => 380,
                'content' => 'مقدمة ترحيبية من الدكتور أندرو إنغ حول ما ستتعلمه في هذا المقرر وكيفية تحقيق أقصى استفادة منه.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
                'is_preview' => true,
            ]
        );

        Lesson::firstOrCreate(
            ['module_id' => $module1->id, 'sort_order' => 2],
            [
                'title' => 'ما هي الشبكة العصبية؟ (What is a Neural Network?)',
                'slug' => 'what-is-neural-network',
                'type' => 'video',
                'duration_seconds' => 540,
                'content' => 'شرح تخطيطي لكيفية محاكاة الخلايا العصبية البيولوجية ونقل الإشارات الرياضية.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
                'is_preview' => true,
            ]
        );

        Lesson::firstOrCreate(
            ['module_id' => $module1->id, 'sort_order' => 3],
            [
                'title' => 'قراءة إثرائية: تاريخ ونشأة خوارزميات الذكاء الاصطناعي',
                'slug' => 'ai-history-and-evolution',
                'type' => 'article',
                'duration_seconds' => 600,
                'content' => "### تاريخ الذكاء الاصطناعي\nبدأت رحلة الذكاء الاصطناعي في خمسينيات القرن الماضي مع اختبار تورينغ، ثم مرت بفترات الشتاء والازدهار حتى ظهور المعالجات الرسومية GPUs الحديثة والبيانات الضخمة.",
                'is_preview' => false,
            ]
        );

        // Lessons for Module 2
        Lesson::firstOrCreate(
            ['module_id' => $module2->id, 'sort_order' => 1],
            [
                'title' => 'بناء دالة التكلفة Cost Function',
                'slug' => 'building-cost-function',
                'type' => 'video',
                'duration_seconds' => 720,
                'content' => 'كيف نقيس مدى دقة النموذج الرياضي ونقلل نسبة الخطأ.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
                'is_preview' => false,
            ]
        );

        $quizLesson = Lesson::firstOrCreate(
            ['module_id' => $module2->id, 'sort_order' => 2],
            [
                'title' => 'الاختبار النهائي الشامل والتقييم المعتمد (Graded Quiz)',
                'slug' => 'deep-learning-final-quiz',
                'type' => 'quiz',
                'duration_seconds' => 1800,
                'content' => 'الاختبار الشامل لتقييم المفاهيم الأساسية للشبكات العصبية والتعلم العميق. اجتياز هذا الاختبار بنسبة 80% فما فوق يمنحك الشهادة الرسمية المعتمدة فوراً.',
                'is_preview' => true,
            ]
        );

        $quiz = Quiz::firstOrCreate(
            ['course_id' => $course->id],
            [
                'lesson_id' => $quizLesson->id,
                'title' => 'الاختبار النهائي الشامل لمساق الشبكات العصبية والتعلم العميق',
                'description' => 'يقيس هذا الاختبار استيعابك للمفاهيم الرياضية والخوارزمية للشبكات العصبية، ودوال التنشيط، ودوال التكلفة، والنزول التدريجي.',
                'passing_percentage' => 80.00,
                'time_limit_minutes' => 30,
                'max_attempts' => 3,
            ]
        );

        $questionsData = [
            [
                'question_text' => 'ما هو الدور الرئيسي لدالة التنشيط (Activation Function) في الشبكة العصبية؟',
                'points' => 2,
                'explanation' => 'دوال التنشيط اللاخطية تتيح للشبكات تعلم دوال وفضاءات معقدة ولا تقتصر على الانحدار الخطي.',
                'options' => [
                    ['option_text' => 'إدخال اللاخطية (Non-linearity) لتمكين الشبكة من تعلم العلاقات المعقدة', 'is_correct' => true],
                    ['option_text' => 'تسريع عملية حساب التفاضل والتكامل فقط', 'is_correct' => false],
                    ['option_text' => 'تخفيض حجم البيانات المدخلة إلى النصف تلقائياً', 'is_correct' => false],
                    ['option_text' => 'منع النموذج من الإفراط في التخصيص بمفردها', 'is_correct' => false],
                ],
            ],
            [
                'question_text' => 'في خوارزمية النزول التدريجي (Gradient Descent)، ماذا يمثل معدل التعلم (Learning Rate \alpha)؟',
                'points' => 2,
                'explanation' => 'معدل التعلم هو المعامل الفائق الذي يتحكم في حجم الخطوة المأخوذة نحو الحد الأدنى لدالة الخسارة.',
                'options' => [
                    ['option_text' => 'حجم الخطوة المتخذة في اتجاه عكس اتجاه التدرج لتحديث الأوزان', 'is_correct' => true],
                    ['option_text' => 'عدد الطبقات المخفية في الشبكة العصبية', 'is_correct' => false],
                    ['option_text' => 'الدقة الكلية للنموذج على بيانات الاختبار', 'is_correct' => false],
                    ['option_text' => 'عدد العينات المعالجة في كل دفعة بيانات Batch', 'is_correct' => false],
                ],
            ],
            [
                'question_text' => 'لماذا لا يُنصح بتهيئة جميع أوزان الشبكة العصبية بالقيمة صفر (Zero Initialization)؟',
                'points' => 2,
                'explanation' => 'تهيئة الأوزان بأصفار تجعل كل العقد المخفية تحسب نفس القيمة وتتلقى نفس التدرجات فلا تنكسر التناظر.',
                'options' => [
                    ['option_text' => 'لأنه يؤدي إلى مشكلة التناظر (Symmetry Problem) حيث تتعلم جميع العقد نفس الميزة', 'is_correct' => true],
                    ['option_text' => 'لأن الحسابات ستؤدي فوراً إلى قسمة غير مقبولة على صفر', 'is_correct' => false],
                    ['option_text' => 'لأن دالة التكلفة تصبح غير قابلة للاشتقاق الرياضي', 'is_correct' => false],
                    ['option_text' => 'لأنه يزيد من زمن تدريب النموذج بأضعاف مضاعفة فقط', 'is_correct' => false],
                ],
            ],
            [
                'question_text' => 'أي من الدوال التالية تُستخدم كمعيار في طبقة الإخراج لمشاكل التصنيف الثنائي (Binary Classification)؟',
                'points' => 2,
                'explanation' => 'دالة السيجمويد تعطي قيماً بين 0 و 1 مما يجعلها تمثل احتمالية الانتماء للفئة الموجبة.',
                'options' => [
                    ['option_text' => 'دالة السيجمويد (Sigmoid Function)', 'is_correct' => true],
                    ['option_text' => 'دالة ReLU التنشيطية', 'is_correct' => false],
                    ['option_text' => 'دالة الخطية التامة Linear', 'is_correct' => false],
                    ['option_text' => 'دالة التانجل tanh بدون تحويل', 'is_correct' => false],
                ],
            ],
            [
                'question_text' => 'ما هي تقنية التنعيم (Regularization) الأكثر شيوعاً لمنع الإفراط في الملاءمة (Overfitting) في الشبكات العميقة؟',
                'points' => 2,
                'explanation' => 'تقنيات L2 regularization و Dropout هي الأشهر في كبح تعقيد الأوزان ومنع الاعتماد المفرط على عقد معينة.',
                'options' => [
                    ['option_text' => 'L2 Regularization (Weight Decay) و تقنية إسقاط العقد (Dropout)', 'is_correct' => true],
                    ['option_text' => 'زيادة عدد الحقب التدريبية (Epochs) لأعلى حد ممكن', 'is_correct' => false],
                    ['option_text' => 'استبعاد بيانات التدريب التي تحتوي قيماً شاذة فقط', 'is_correct' => false],
                    ['option_text' => 'تصفير معدل التعلم بعد أول حقبة تدريبية', 'is_correct' => false],
                ],
            ],
        ];

        foreach ($questionsData as $qIndex => $qData) {
            $question = QuizQuestion::firstOrCreate(
                ['quiz_id' => $quiz->id, 'sort_order' => $qIndex + 1],
                [
                    'question_text' => $qData['question_text'],
                    'type' => 'single_choice',
                    'points' => $qData['points'],
                    'explanation' => $qData['explanation'],
                ]
            );

            foreach ($qData['options'] as $optIndex => $optData) {
                QuizQuestionOption::firstOrCreate(
                    ['question_id' => $question->id, 'sort_order' => $optIndex + 1],
                    [
                        'option_text' => $optData['option_text'],
                        'is_correct' => $optData['is_correct'],
                    ]
                );
            }
        }

        // Course 2: UX Design
        $uxCourse = Course::firstOrCreate(
            ['slug' => 'foundations-of-user-experience-ux-design'],
            [
                'title' => 'أساسيات تصميم تجربة المستخدم (Foundations of UX Design)',
                'headline' => 'تعلم مبادئ التفكير التصميمي، وإجراء المقابلات، وتحديد نقاط ألم المستخدمين.',
                'description' => 'دورة مكثفة تغطي رحلة المستخدم وسهولة الوصول والتصميم الشامل مع تطبيقات عملية.',
                'instructor_id' => $instructor->id,
                'category_id' => $uxCategory->id,
                'level' => 'beginner',
                'language' => 'ar',
                'price' => 29.00,
                'duration_hours' => 18,
                'is_published' => true,
            ]
        );

        $uxModule = CourseModule::firstOrCreate(
            ['course_id' => $uxCourse->id, 'sort_order' => 1],
            [
                'title' => 'الوحدة الأولى: مدخل إلى التفكير التصميمي (Design Thinking)',
                'description' => 'المراحل الخمس للتفكير التصميمي وكيفية بناء منتجات تلبي رغبات المستخدمين الحقيقية.',
            ]
        );

        Lesson::firstOrCreate(
            ['module_id' => $uxModule->id, 'sort_order' => 1],
            [
                'title' => 'مقدمة في تجربة المستخدم UX والواجهات UI',
                'slug' => 'intro-to-ux-and-ui',
                'type' => 'video',
                'duration_seconds' => 420,
                'content' => 'الفرق الجوهري بين الواجهة المرئية وتجربة التفاعل الكلية.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
                'is_preview' => true,
            ]
        );

        // 6. Sample Course Reviews
        $student = User::where('email', 'student@coursura.com')->first();
        if ($student && $course) {
            \App\Models\CourseReview::firstOrCreate(
                ['user_id' => $student->id, 'course_id' => $course->id],
                [
                    'rating' => 5,
                    'review_text' => 'مساق استثنائي وشرح عميق جداً للرياضيات الكامنة وراء الشبكات العصبية. أفضل كورس عربي في التعلم العميق بلا منازع!',
                    'is_published' => true,
                ]
            );
        }
    }
}
