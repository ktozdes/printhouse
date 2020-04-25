
SELECT name, plate_id , sum(quantity), count(id) FROM `storages` 
    where name = 'storage' 
    GROUP by plate_id 

union 

SELECT name, plate_id , sum(quantity), count(id) FROM `storages`
    where name = 'order' 
    GROUP by plate_id

ORDER BY `plate_id` ASC



select inp.plate_id, inp.id, inp.quantity, inp.updated_at, min_value from storages as inp 
left join 
    (select used_storage_id, min(local_quantity_after) as min_value from storages group by used_storage_id) 
        as outp on inp.id = outp.used_storage_id 
where inp.name = 'storage' and plate_id = 1 and (min_value > 0 OR min_value is null)



select orders.id, storage.storage_id, storage.plate_id, plate.price as plate_price, plate_user.price as user_price from orders
left join (select id as storage_id, order_id, plate_id as plate_id from storages where order_id = 101 LIMIT 1)as storage on storage.order_id = orders.id
left join plates as plate on plate.id = storage.plate_id
left join plate_user on plate_user.user_id = orders.user_id and plate_user.plate_id = storage.plate_id

where orders.id = 101



SELECT sum(storages.quantity) as quantity, users.name as manager_name, sum( abs( payments.amount ) ) as amount,concat_ws(' ', YEAR(storages.updated_at), MONTHNAME(storages.updated_at)) as date_name
FROM `storages` 
inner join users on users.id = storages.manager_id
left join orders on orders.id = storages.order_id
left join payments on payments.id = orders.payment_id

where orders.status_id = 3 and storages.updated_at >= '2020-03-01 18:16:26'

group by concat_ws(' ', YEAR(storages.updated_at), MONTHNAME(storages.updated_at)), users.name
order by concat_ws(' ', YEAR(storages.updated_at), MONTHNAME(storages.updated_at)), users.name



select users.name as manager_name,
SUM(IF(storages.name = 'order' AND orders.status_id = 3, storages.quantity, 0)) AS order_quantity,
SUM(IF(storages.name = 'defect',storages.quantity, 0)) AS defect_quantity,
SUM(IF(storages.name = 'order' AND payments.amount IS NOT NULL, abs( payments.amount ) , 0)) AS order_price,
SUM(IF(storages.name = 'defect',storages.quantity * storages.price, 0)) AS defect_price,
sum(storages.quantity) as total_quantity,

concat_ws('-', YEAR(storages.updated_at), MONTH(storages.updated_at)) as date_name from `storages` inner join `users` on
`users`.`id` = `storages`.`manager_id` left join `orders` on `orders`.`id` = `storages`.`order_id` and
`orders`.`status_id` = 3 left join `payments` on `payments`.`id` = `orders`.`payment_id` where (`storages`.`updated_at`
>= '2018-05-17 23:16:59' and `storages`.`updated_at` <= '2020-05-17 23:16:59') and (`storages`.`name`='order' or `storages`.`name`='defect') group by concat_ws('-',
    YEAR(storages.updated_at), MONTH(storages.updated_at)), users.name order by concat_ws('-',
    YEAR(storages.updated_at), MONTH(storages.updated_at)) desc, users.name