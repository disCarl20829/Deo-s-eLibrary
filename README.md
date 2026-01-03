**--//MYSQL DATABASE\\--**
Match your database and tables exactly.

_[book_category]_
category_id (not pri key/auto inc)
book_id

_[customer_feedbacks]_
feedback_id (pri key)
user_id
feedback_message
feedback_rating
feedback_date

_[save_books]_
saved_id (pri key)
user_id
book_id
save_quantity

_[shop]_
shop_id (pri key)
shop_owner
shop_history
shop_mission
shop_vision
shop_img_path

_[users]_
user_id
user_name
user_password
user_description (redacted)

_[books]_
book_id
book_title
book_author
book_pubdate
book_description
book_price
book_img_path

**--//END\\--**

- You must copy all files including folders for the images
- Modify the **$db** of your **db_connect** with the exact database name (e.g mine was $db = "elibrary")
