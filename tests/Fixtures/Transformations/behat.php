<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('attributes'))
        ->withSuite(
            (new Suite('simple_step_argument_transformation'))
            ->withPaths(
                'features/simple_step_argument_transformation.feature'
            )
            ->withContexts(
                'Simple_Transformation_Attributes_Context',
                'User_Context'
            )
        )
        ->withSuite(
            (new Suite('step_argument_transformation_without_parameters'))
            ->withPaths(
                'features/step_argument_transformation_without_parameters.feature'
            )
            ->withContexts(
                'Transformation_Without_Parameters_Attributes_Context',
                'User_Context'
            )
        )
        ->withSuite(
            (new Suite('multiple_transformations_in_one_function'))
            ->withPaths(
                'features/multiple_transformations_in_one_function.feature'
            )
            ->withContexts(
                'Multiple_Transformations_In_One_Function_Attributes_Context',
                'User_Context'
            )
        )
        ->withSuite(
            (new Suite('table_column_argument_transformation'))
                ->withPaths(
                    'features/table_column_argument_transformation.feature'
                )
                ->withContexts(
                    'Column_Transformation_Context',
                    'User_Context'
                )
        )
    )
    ->withProfile(
        (new Profile('annotations'))
        ->withSuite(
            (new Suite('simple_step_argument_transformation'))
            ->withPaths(
                'features/simple_step_argument_transformation.feature'
            )
            ->withContexts(
                'Transformation_Annotations_Context',
                'User_Context'
            )
        )
        ->withSuite(
            (new Suite('table_argument_transformation'))
            ->withPaths(
                'features/table_argument_transformation.feature'
            )
            ->withContexts(
                'Transformation_Annotations_Context',
                'User_Context'
            )
        )
        ->withSuite(
            (new Suite('row_table_argument_transformation'))
            ->withPaths(
                'features/row_table_argument_transformation.feature'
            )
            ->withContexts(
                'Transformation_Annotations_Context',
                'User_Context'
            )
        )
        ->withSuite(
            (new Suite('table_row_argument_transformation'))
            ->withPaths(
                'features/table_row_argument_transformation.feature'
            )
            ->withContexts(
                'Transformation_Annotations_Context',
                'User_Context'
            )
        )
        ->withSuite(
            (new Suite('whole_table_argument_transformation'))
            ->withPaths(
                'features/whole_table_argument_transformation.feature'
            )
            ->withContexts(
                'Whole_Table_Annotations_Context'
            )
        )
        ->withSuite(
            (new Suite('named_argument_transformation'))
            ->withPaths(
                'features/named_argument_transformation.feature'
            )
            ->withContexts(
                'Transformation_Annotations_Context',
                'User_Context'
            )
        )
        ->withSuite(
            (new Suite('transform_different_types'))
            ->withPaths(
                'features/transform_different_types.feature'
            )
            ->withContexts(
                'Multiple_Types_Annotations_Context'
            )
        )
        ->withSuite(
            (new Suite('by_type_object_transformation'))
            ->withPaths(
                'features/by_type_object_transformation.feature'
            )
            ->withContexts(
                'By_Type_Annotations_Context'
            )
        )
        ->withSuite(
            (new Suite('by_type_and_by_name_object_transformation'))
            ->withPaths(
                'features/by_type_and_by_name_object_transformation.feature'
            )
            ->withContexts(
                'By_Type_And_By_Name_Annotations_Context'
            )
        )
        ->withSuite(
            (new Suite('unicode_named_argument_transformation'))
            ->withPaths(
                'features/unicode_named_argument_transformation.feature'
            )
            ->withContexts(
                'Transformation_Annotations_Context',
                'User_Context'
            )
        )
        ->withSuite(
            (new Suite('ordinal_argument_transformation'))
            ->withPaths(
                'features/ordinal_argument_transformation.feature'
            )
            ->withContexts(
                'Ordinal_Transformation_Annotations_Context'
            )
        )
        ->withSuite(
            (new Suite('by_type_union_transformation'))
            ->withPaths(
                'features/by_type_union_transformation.feature'
            )
            ->withContexts(
                'By_Type_Union_Annotations_Context'
            )
        )
        ->withSuite(
            (new Suite('scalar_type_transformation'))
            ->withPaths(
                'features/scalar_type_transformation.feature'
            )
            ->withContexts(
                'Scalar_Type_Annotations_Context'
            )
        )
    )
;
