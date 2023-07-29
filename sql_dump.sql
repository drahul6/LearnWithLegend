/*
 
 Todo -----
 Admin management -- donw
 communication -done
 Countdown timer -done
 hide / show form inputs relating to the type of goods physical | digital advance / ---done
 clear location
 emails where necessary
 
 
 */
ALTER TABLE `products`
ADD `stock` INT NULL DEFAULT NULL
AFTER `data`;
ALTER TABLE `cs_support_tickets` CHANGE `user_id` `user_id` LONGTEXT NULL DEFAULT NULL;