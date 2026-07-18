# Animals Module — Activity Diagrams

## Create an animal (`ManagesAnimals::store()`)

```
                    (start)
                       |
                       v
              [validate request:
               name/weight/species/
               health/age/gender/
               rescueID/slotID/images]
                       |
                  <invalid?>----yes---> [return validation errors]
                       | no
                       v
              [begin reporting-connection
               transaction]
                       |
                       v
                <slotID provided?>
                  |            |
                 yes           no
                  |            |
        <slot exists AND        |
         status == available?>  |
          |          |          |
          no        yes         |
          |          |          |
  [rollback;   [continue]       |
   return                       |
   "slot not                    |
   available"]                  |
          |          \         /
          |           \       /
          |            v     v
          |     [sp_animal_create]
          |            |
          |       <images uploaded?>
          |          |         |
          |         yes        no
          |          |         |
          |   [for each file:  |
          |    upload to        |
          |    Cloudinary,      |
          |    Image::create]   |
          |          |         |
          |           \       /
          |            v     v
          |     [commit transaction]
          |            |
          |            v
          |     [redirect to create
          |      form, same rescue,
          |      success flash]
          |            |
           \___________|
                        v
                     (end)

  (any exception after the transaction opened -> rollback, destroy any
   already-uploaded Cloudinary images, return with an error flash)
```

## Delete an animal (`ManagesAnimals::destroy()`)

```
(start)
   |
   v
<reporting DB online?> --no--> [skip image cleanup, note in message]
   | yes
   v
[delete each Cloudinary image + Image row]
   |
   v
[sp_animal_delete]
   |
   v
<animal held a slotID AND shelter DB online?>
   |                              |
  yes                             no
   |                              |
[recount animals on that slot]    |
   |                              |
<remaining >= capacity?>          |
   |            |                 |
[occupied]  [available]           |
   |            |                 |
    \__________/___________ _____/
               |
               v
       [commit both open transactions]
               |
               v
     [redirect to index, success flash]
               |
              (end)

  (any exception -> roll back whichever of reporting/shelter transactions
   were opened, return with an error flash)
```

## Adopter match calculation (`ManagesMatching::getMatches()`)

```
(start)
   |
   v
<force_refresh param present?> --yes--> [clear connection-status cache]
   | no
   v
<users DB online?> --no--> [503: users database offline] --> (end)
   | yes
<animals DB online?> --no--> [503: animal database offline] --> (end)
   | yes
   v
<AdopterProfile exists for this user?> --no--> [200: "complete your
   |                                              profile first"] --> (end)
  yes
   v
<cache has "animal_matches_user_{id}" AND not force_refresh?>
   |                                              |
  yes                                             no
   |                                              |
[return cached matches, cached=true]     [fetch up to 20 animals:
   |                                       Not Adopted, has a profile]
   |                                              |
   |                                       <any candidates?>
   |                                        |            |
   |                                       no            yes
   |                                        |            |
   |                              [return empty      [for each candidate:
   |                               matches list]       calculateMatchScore()
   |                                        |           (species/energy/
   |                                        |            housing/size/
   |                                        |            children/pets),
   |                                        |            skip on error]
   |                                        |            |
   |                                        |     [sort by score desc,
   |                                        |      keep top 5]
   |                                        |            |
   |                                        |     [cache for 300s]
   |                                        |            |
    \_______________________________________\___________/
                                                          |
                                                          v
                                              [return matches as JSON]
                                                          |
                                                         (end)

  Note: only the FIRST 20 candidates (in default PK order, no ORDER BY) are
  ever scored — a genuinely better match beyond that window is never
  surfaced. This is a known, deliberately-unfixed limitation (see
  docs/08-testing.md), not a bug in this diagram.
```
