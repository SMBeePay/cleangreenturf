<?php
/**
 * East DFW turf-installation city pages (install SEO plan, Section 4 "CITY
 * UNIQUE BLOCKS"). Copy is the owner-supplied spec text. Rendered by
 * content/pages/install-city.php for /turf-installation/{slug}.
 *
 * {fact:id} inside a string is replaced with that site fact at render time.
 * 'neighbors' are slugs; FAQ answers are HTML.
 *
 * Owner verification note (from the spec): these local details come from
 * general knowledge of each area. Andrew should read each one and change
 * anything that isn't right for the neighborhoods he actually works in.
 *
 * Changed from the spec: Royse City's opening line said "Royse City is our
 * home base". Decision D1 (Nov 24) kept McKinney as the business address,
 * so that claim was dropped. Sachse's opening line mentioned "the minimum
 * job size"; there is no minimum (Andrew, Nov 24), so that phrase was dropped.
 */
return [
    'royse-city' => [
        'name' => 'Royse City',
        'opening_line' => 'A lot of the yards we see in Royse City are brand new.',
        'local_conditions' => 'Royse City has grown quickly, with many new-build neighborhoods where the builder leaves a thin layer of sod or bare dirt. The city spans parts of Rockwall, Collin and Hunt counties. Much of the soil in this part of North Texas is heavy clay that holds water and moves with the weather, so the base and grading do most of the work. If your yard floods after a hard rain or has settled since move-in, tell us before we quote. We deal with that during the base build.',
        'hoa' => 'Most newer Royse City subdivisions have an HOA with an architectural review process. Ask for the written rules on artificial turf before you buy. Some HOAs allow turf in back yards only. We can provide a project description and product samples for your submission.',
        'cost_note' => 'New-construction lots are often open and level, which keeps many Royse City jobs in the lower half of the range. A sloped lot or a tight side gate moves it up.',
        'neighbors' => ['rockwall', 'fate', 'forney'],
        'faq' => [
            ['Do you work on new-build yards in Royse City?', 'Yes. New builds often have uneven grading and compacted builder fill. We assess both and quote the base work separately in your estimate.'],
            ['Can you install turf in a yard that floods?', "Often, yes, but drainage has to be fixed first. We'll look at where the water goes and plan the base around it."],
        ],
    ],
    'rockwall' => [
        'name' => 'Rockwall',
        'opening_line' => 'Rockwall has older neighborhoods with mature trees and newer ones near the lake, and each needs a different plan.',
        'local_conditions' => 'Rockwall is the county seat, and homes range from established neighborhoods with mature trees to newer subdivisions closer to Lake Ray Hubbard. Tree roots, shade and grade changes all affect how we plan turf. Where trees shade the yard, turf tends to stay cooler. Where the lot slopes, the base and drainage plan matter more. Soil in the area is clay-heavy, so we build the base to move water off the surface.',
        'hoa' => 'Many Rockwall neighborhoods have an HOA, and some have strict architectural review. Confirm your rules on artificial turf before you sign anything. We can give you samples and a written scope for the submission.',
        'cost_note' => 'Mature lots with trees, slope or limited access often fall in the middle or upper part of the range.',
        'neighbors' => ['heath', 'fate', 'royse-city'],
        'faq' => [
            ['Can you install turf around mature trees?', 'Usually, yes. We cut around trunks and protect roots when we set the base. Tell us which trees must stay.'],
            ['Does turf work on a sloped Rockwall lot?', 'Yes, with the right base and edging. Slope adds labor, so it affects the price.'],
        ],
    ],
    'heath' => [
        'name' => 'Heath',
        'opening_line' => 'Heath lots are bigger than most, which changes how a turf project is planned and priced.',
        'local_conditions' => 'Heath is known for larger lots and homes set back from the street. Larger yards mean more square footage, longer runs of edging and more thought about how material gets to the backyard. Bigger projects usually benefit from planning in zones (a main lawn, a pet area, a play area) instead of covering everything. Clay soil and drainage on large lots deserve a close look.',
        'hoa' => 'Many Heath neighborhoods have an HOA or architectural committee. Larger turf installs are more likely to need approval. Ask for the rules in writing. We can prepare a project description and samples for your submission.',
        'cost_note' => 'Larger areas can bring the per-square-foot figure down, though extra access distance and grading can push it back up. Total project cost is higher because of the size.',
        'neighbors' => ['rockwall', 'fate', 'royse-city'],
        'faq' => [
            ['Should I cover my whole yard in turf?', "Not always. On a large lot, many homeowners turf the main use areas and leave the rest as natural landscape. We'll talk it through."],
            ['Do larger projects cost less per square foot?', 'Sometimes. Setup costs spread out over more area, but grading and access can offset that.'],
        ],
    ],
    'fate' => [
        'name' => 'Fate',
        'opening_line' => 'Fate is mostly newer neighborhoods, and the yards we see here usually start as builder-grade dirt or thin sod.',
        'local_conditions' => "Fate has grown with new subdivisions, so many yards are small to mid-sized, with fresh builder grading. New yards settle in their first years, and that matters for turf because the base has to be compacted properly and graded for drainage. Heavy clay is common in the area. If your yard has low spots now, we'll plan for them.",
        'hoa' => 'Most Fate subdivisions have an HOA. Review your rules on artificial turf, fencing and rear-yard visibility before you choose a design. We can help with the submission package.',
        'cost_note' => 'Smaller, simple, open backyards often land in the lower to middle part of the range.',
        'neighbors' => ['rockwall', 'royse-city', 'heath'],
        'faq' => [
            ['Is turf a good idea for a new house in Fate?', "Many owners choose it because builder yards are uneven and slow to fill in. Tell us how long the home has been settling and we'll check the grade."],
            ['Can I install turf in stages?', 'Yes. Some owners start with the main lawn and add a pet area or side yard later. We can plan for it so the pieces match.'],
        ],
    ],
    'forney' => [
        'name' => 'Forney',
        'opening_line' => 'Forney is growing fast, and a lot of new homes there come with bare or patchy yards.',
        'local_conditions' => 'Forney sits in Kaufman County, with substantial new construction around town. Many yards are builder-graded with compacted fill and heavy clay beneath. A turf install here starts by fixing how water leaves the yard, then building the base on top of that. Open lots are common, which helps access and cost.',
        'hoa' => 'Many Forney neighborhoods have an HOA. Check your rules on artificial turf and get approval in writing before work begins. We can prepare a scope and samples.',
        'cost_note' => 'Open new-build yards with good access tend to price in the lower to middle of the range.',
        'neighbors' => ['royse-city', 'rockwall', 'fate'],
        'faq' => [
            ['My Forney yard is bare dirt. Can you install turf right away?', "Often, yes. We'll check the grade and drainage first so the base is built correctly."],
            ['How does turf do on compacted builder fill?', 'Compaction can help the base but can also trap water. We look at drainage before deciding how deep to dig.'],
        ],
    ],
    'wylie' => [
        'name' => 'Wylie',
        'opening_line' => 'Wylie has a mix of established neighborhoods and new builds, and a lot of dog owners.',
        'local_conditions' => 'Wylie has older neighborhoods with mature trees and newer subdivisions, which means different yard conditions across town. Shade from mature trees affects turf temperature and where grass struggles to grow. Pet owners are a big part of the demand here, so drainage and odor planning come up constantly. Clay soil is common, and the base is what keeps the surface drained.',
        'hoa' => 'HOA rules vary a lot between Wylie neighborhoods. Check yours first. Older neighborhoods may have no HOA but may still have city rules about what goes in front yards.',
        'cost_note' => 'Established yards with trees or old landscaping to remove often land in the middle of the range.',
        'neighbors' => ['sachse', 'mckinney', 'royse-city'],
        'faq' => [
            ["My lawn won't grow under my trees. Is turf an option?", "Yes. Turf doesn't need sun to look good. We protect the tree roots when we set the base."],
            ['Can you build a dog area in the corner of my yard?', 'Yes. A dedicated pet area with drainage and a cleaning plan is one of our most common requests. See {link:/turf-installation/pet-turf|pet turf}.'],
        ],
    ],
    'mckinney' => [
        'name' => 'McKinney',
        'opening_line' => 'McKinney homes range from historic neighborhoods to large HOA communities, so rules and conditions vary from street to street.',
        'local_conditions' => 'McKinney is a large city with a wide mix of older neighborhoods and newer communities. Yard size, soil, grading and access vary. Newer communities often have uniform lots and clear HOA rules. Older neighborhoods can have mature trees, odd shapes and tighter access.',
        'hoa' => 'Most McKinney communities have an HOA, and many have a formal architectural review process. Expect to submit a description, a site plan and product samples. We can help put the package together. Do not start work before you have written approval.',
        'cost_note' => 'A uniform, open lot is simple to quote. Older lots with shape, trees or tight gates move toward the upper end.',
        'neighbors' => ['wylie', 'sachse', 'royse-city'],
        'faq' => [
            ['What do McKinney HOAs usually ask for?', 'Typically a written description, a site plan, and product samples. Rules differ by community, so get yours in writing.'],
            ['Do you install in front yards?', 'Only where the HOA and city allow it. Many neighborhoods restrict front-yard turf, so check before you plan.'],
        ],
    ],
    'sachse' => [
        'name' => 'Sachse',
        'opening_line' => 'Sachse yards tend to be smaller, which makes planning matter.',
        'local_conditions' => "Sachse is a smaller suburb with established neighborhoods and mid-sized lots. Smaller yards can be simple to turf but cost slightly more per square foot because setup doesn't shrink with the yard. Mature trees, older landscaping and tight side gates are common. Clay soil is typical, so base and drainage get the same attention as anywhere else.",
        'hoa' => "Many Sachse neighborhoods have HOAs. Some older ones don't. Check before you plan, and if you have an HOA, get approval in writing.",
        'cost_note' => 'Smaller yards, especially under about 500 square feet, can price toward the upper end because fixed setup costs spread over less area. {fact:min_job_sqft}',
        'neighbors' => ['wylie', 'mckinney', 'rockwall'],
        'faq' => [
            ['Is my yard too small for turf?', "Not necessarily. Many small yards are good candidates. Send us the size and we'll tell you where the price lands."],
            ['Can you turf just a side yard or dog run?', 'Yes. Small areas are common. Pricing for smaller areas can run toward the upper end of the range.'],
        ],
    ],
];
