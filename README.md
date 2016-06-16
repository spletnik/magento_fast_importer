# Magento Fast Importer
## Description
From now on you will have no problems importing and updating all your products in your magento shop. Blazing Fast Importer will solve all your import problems.Multiple profiles with multiple options will satisfy all your needs.

Operates directly in SQL and is the result of a deep analysis of the Magento Database Model. It can create products or update an existing catalog and deal with HUGE data (Millions of products could be managed, but even above a few 1000’s, you’ll see the real difference).

## Features
- It provides high speed compared to Dataflow (depending on server config & number of attributes, 70-100 rows/sec is standard speed).
- It supports Dataflow export CSVs file & also some enhanced CSV syntax for dealing with custom options import & media gallery import. 
- It works for multistore
- It supports remote image urls for image related attributes (in this case, speed is affected by image download)
- Can handle simple AND configurable products
- Can not handle downloadable nor bundled product types yet
- Can create any amount of profiles
- 3 different import options
    - Create and Update products
    - Create new, skip existing
    - Update, skip existing
    - Auto detect attributes
    - Mapping attributes
    
## Implementation
- Extension is compatible with: 1.3.x, 1.4.x, 1.5.x, 1.6.x, 1.7.x, 1.8x, 1.9.x

## Manual
You can download manual [here](doc/manual-v1.4.pdf)
