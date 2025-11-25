-- DROP FUNCTION public.sp_check_access_to_watching_course(int8, int8);

CREATE OR REPLACE FUNCTION public.sp_check_access_to_watching_course(v_euser_id bigint, v_course_id bigint)
    RETURNS TABLE(
       id bigint,
       teacher_id bigint,
       is_corporative bigint,
       b2b_b2c integer,
       company_name text,
       corp_course_type integer,
       corporative_user_id bigint,
       duty_id bigint,
       sold_course_id bigint,
       corporative_group_user_id bigint,
       is_mine boolean
    )
    LANGUAGE sql
        AS $function$
    select
        c.id,
        c.eusers_id as teacher_id,
        c.is_corporative,
        c.b2b_b2c,
        cc.name as company_name,
        cc.corp_course_type,
        cc.corporative_user_id,
        cc.duty_id,
        sc.id as sold_course_id,
        cg.corporative_group_user_id,
        CASE WHEN c.eusers_id = v_euser_id THEN true ELSE false END as is_mine
    from courses as c
    -- teacher
    left join eusers as e on e.id = c.eusers_id
    -- sold course
    left join (
        select sc.id, sc.course_id, sc.status
        from sold_courses as sc
        where sc.course_id = v_course_id and sc.euser_id = v_euser_id and sc.status = 1
    ) as sc on sc.course_id = c.id
    -- company
    left join (
        select
            cc.id,
            cc.euser_id,
            cc.name,
            cc.logo,
            cco.type as corp_course_type,
            cu.id as corporative_user_id,
            cu.duty_id
        from corporative_companies as cc
        left join corporative_users as cu on (cu.company_id = cc.id and cu.euser_id = v_euser_id)
        join corporative_courses as cco on (cco.company_id = cc.id and cco.course_id = v_course_id)
    ) as cc on cc.id = c.is_corporative
    -- company group
    left join (
        select
            cgu.id as corporative_group_user_id,
            cgc.course_id
        from corporative_group_users as cgu
        left join corporative_groups as cg on cg.id = cgu.group_id
        left join corporative_group_courses as cgc on cgc.group_id = cg.id
        where cgc.course_id = v_course_id and cgu.euser_id = v_euser_id
    ) as cg on cg.course_id = c.id
    where c.id = v_course_id and c.status in (1,2) and c.completed = 1
    limit 1;
$function$;
