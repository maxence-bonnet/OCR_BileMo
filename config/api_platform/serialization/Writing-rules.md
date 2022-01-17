# Writing rules for Groups order

 - 1) [current entity operation] > [foreign entity operation]
 - 2) [read operation] > [update operation] > [replace operation] > [write operation]
 - 3) [item operation] > [collection operation]

 ## Example

 given :
  - read:Brand:collection => current & read & collection
  - read:Brand:item => current & read & item
  - read:Phone:item => foreign & read & item
  - write:Brand => current & write & item
  - read:Phone:collection => foreign & read & collection
  - write:Phone => foreign & write & item

in :
 - Brand groups configration

result :

### groups: [read:Brand:item, read:Brand:collection, write:Brand, read:Phone:item, read:Phone:collection, write:Phone]