<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->with_profile(
        (new Profile('attributes'))
        ->with_suite(
            (new Suite('simple_step_argument_transformation'))
            ->with_paths(
                'features/simple_step_argument_transformation.feature'
            )
            ->with_contexts(
                'Simple_Transformation_Attributes_Context',
                'User_Context'
            )
        )
        ->with_suite(
            (new Suite('step_argument_transformation_without_parameters'))
            ->with_paths(
                'features/step_argument_transformation_without_parameters.feature'
            )
            ->with_contexts(
                'Transformation_Without_Parameters_Attributes_Context',
                'User_Context'
            )
        )
        ->with_suite(
            (new Suite('multiple_transformations_in_one_function'))
            ->with_paths(
                'features/multiple_transformations_in_one_function.feature'
            )
            ->with_contexts(
                'Multiple_Transformations_In_One_Function_Attributes_Context',
                'User_Context'
            )
        )
        ->with_suite(
            (new Suite('table_column_argument_transformation'))
                ->with_paths(
                    'features/table_column_argument_transformation.feature'
                )
                ->with_contexts(
                    'Column_Transformation_Context',
                    'User_Context'
                )
        )
    )
    ->with_profile(
        (new Profile('annotations'))
        ->with_suite(
            (new Suite('simple_step_argument_transformation'))
            ->with_paths(
                'features/simple_step_argument_transformation.feature'
            )
            ->with_contexts(
                'Transformation_Annotations_Context',
                'User_Context'
            )
        )
        ->with_suite(
            (new Suite('table_argument_transformation'))
            ->with_paths(
                'features/table_argument_transformation.feature'
            )
            ->with_contexts(
                'Transformation_Annotations_Context',
                'User_Context'
            )
        )
        ->with_suite(
            (new Suite('row_table_argument_transformation'))
            ->with_paths(
                'features/row_table_argument_transformation.feature'
            )
            ->with_contexts(
                'Transformation_Annotations_Context',
                'User_Context'
            )
        )
        ->with_suite(
            (new Suite('table_row_argument_transformation'))
            ->with_paths(
                'features/table_row_argument_transformation.feature'
            )
            ->with_contexts(
                'Transformation_Annotations_Context',
                'User_Context'
            )
        )
        ->with_suite(
            (new Suite('whole_table_argument_transformation'))
            ->with_paths(
                'features/whole_table_argument_transformation.feature'
            )
            ->with_contexts(
                'Whole_Table_Annotations_Context'
            )
        )
        ->with_suite(
            (new Suite('named_argument_transformation'))
            ->with_paths(
                'features/named_argument_transformation.feature'
            )
            ->with_contexts(
                'Transformation_Annotations_Context',
                'User_Context'
            )
        )
        ->with_suite(
            (new Suite('transform_different_types'))
            ->with_paths(
                'features/transform_different_types.feature'
            )
            ->with_contexts(
                'Multiple_Types_Annotations_Context'
            )
        )
        ->with_suite(
            (new Suite('by_type_object_transformation'))
            ->with_paths(
                'features/by_type_object_transformation.feature'
            )
            ->with_contexts(
                'By_Type_Annotations_Context'
            )
        )
        ->with_suite(
            (new Suite('by_type_and_by_name_object_transformation'))
            ->with_paths(
                'features/by_type_and_by_name_object_transformation.feature'
            )
            ->with_contexts(
                'By_Type_And_By_Name_Annotations_Context'
            )
        )
        ->with_suite(
            (new Suite('unicode_named_argument_transformation'))
            ->with_paths(
                'features/unicode_named_argument_transformation.feature'
            )
            ->with_contexts(
                'Transformation_Annotations_Context',
                'User_Context'
            )
        )
        ->with_suite(
            (new Suite('ordinal_argument_transformation'))
            ->with_paths(
                'features/ordinal_argument_transformation.feature'
            )
            ->with_contexts(
                'Ordinal_Transformation_Annotations_Context'
            )
        )
        ->with_suite(
            (new Suite('by_type_union_transformation'))
            ->with_paths(
                'features/by_type_union_transformation.feature'
            )
            ->with_contexts(
                'By_Type_Union_Annotations_Context'
            )
        )
        ->with_suite(
            (new Suite('scalar_type_transformation'))
            ->with_paths(
                'features/scalar_type_transformation.feature'
            )
            ->with_contexts(
                'Scalar_Type_Annotations_Context'
            )
        )
    )
;
