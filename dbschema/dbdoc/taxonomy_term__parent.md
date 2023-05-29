# taxonomy_term__parent

## Description

Data storage for taxonomy_term field parent.

<details>
<summary><strong>Table Definition</strong></summary>

```sql
CREATE TABLE `taxonomy_term__parent` (
  `bundle` varchar(128) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT '' COMMENT 'The field instance bundle to which this row belongs, used when deleting a field instance',
  `deleted` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'A boolean indicating whether this data item has been deleted',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'The entity id this data is attached to',
  `revision_id` int(10) unsigned NOT NULL COMMENT 'The entity revision id this data is attached to',
  `langcode` varchar(32) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT '' COMMENT 'The language code for this data item.',
  `delta` int(10) unsigned NOT NULL COMMENT 'The sequence number for this data item, used for multi-value fields',
  `parent_target_id` int(10) unsigned NOT NULL COMMENT 'The ID of the target entity.',
  PRIMARY KEY (`entity_id`,`deleted`,`delta`,`langcode`),
  KEY `revision_id` (`revision_id`),
  KEY `parent_target_id` (`parent_target_id`),
  KEY `bundle_delta_target_id` (`bundle`,`delta`,`parent_target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Data storage for taxonomy_term field parent.'
```

</details>

## Columns

| Name | Type | Default | Nullable | Children | Parents | Comment |
| ---- | ---- | ------- | -------- | -------- | ------- | ------- |
| bundle | varchar(128) | '' | false |  |  | The field instance bundle to which this row belongs, used when deleting a field instance |
| deleted | tinyint(4) | 0 | false |  |  | A boolean indicating whether this data item has been deleted |
| entity_id | int(10) unsigned |  | false |  |  | The entity id this data is attached to |
| revision_id | int(10) unsigned |  | false |  |  | The entity revision id this data is attached to |
| langcode | varchar(32) | '' | false |  |  | The language code for this data item. |
| delta | int(10) unsigned |  | false |  |  | The sequence number for this data item, used for multi-value fields |
| parent_target_id | int(10) unsigned |  | false |  |  | The ID of the target entity. |

## Constraints

| Name | Type | Definition |
| ---- | ---- | ---------- |
| PRIMARY | PRIMARY KEY | PRIMARY KEY (entity_id, deleted, delta, langcode) |

## Indexes

| Name | Definition |
| ---- | ---------- |
| bundle_delta_target_id | KEY bundle_delta_target_id (bundle, delta, parent_target_id) USING BTREE |
| parent_target_id | KEY parent_target_id (parent_target_id) USING BTREE |
| revision_id | KEY revision_id (revision_id) USING BTREE |
| PRIMARY | PRIMARY KEY (entity_id, deleted, delta, langcode) USING BTREE |

## Relations

![er](taxonomy_term__parent.svg)

---

> Generated by [tbls](https://github.com/k1LoW/tbls)