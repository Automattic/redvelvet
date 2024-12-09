import json
import random

# Generate large-scale NPF to Blocks and mixed type data based on diverse examples
def generate_massive_test_data(file_path, count=1000000):
    data = {
        "npf_posts": [],
        "html_blocks": []
    }

    for i in range(count):
        content_items = []
        html_items = []

        # Randomly decide the number of content types in a single post (between 2 to 5 types)
        num_content_types = random.randint(2, 5)
        content_types = random.choices(["paragraph", "heading1", "heading2", "quote", "chat", "quirky", "formatted", "mixed"], k=num_content_types)

        for type_choice in content_types:
            if type_choice == "paragraph":
                # Paragraph
                content_items.append({
                    "type": "text",
                    "text": f"This is a paragraph {i}"
                })
                html_items.append(f"<!-- wp:paragraph --><p>This is a paragraph {i}</p><!-- /wp:paragraph -->")

            elif type_choice == "heading1":
                # Heading level 1
                content_items.append({
                    "type": "text",
                    "text": f"This is a heading level 1 {i}",
                    "subtype": "heading1"
                })
                html_items.append(f"<!-- wp:heading {{\"level\":1}} --><h1 class=\"wp-block-heading\">This is a heading level 1 {i}</h1><!-- /wp:heading -->")

            elif type_choice == "heading2":
                # Heading level 2
                content_items.append({
                    "type": "text",
                    "text": f"This is a heading level 2 {i}",
                    "subtype": "heading2"
                })
                html_items.append(f"<!-- wp:heading --><h2 class=\"wp-block-heading\">This is a heading level 2 {i}</h2><!-- /wp:heading -->")

            elif type_choice == "quote":
                # Quote
                content_items.append({
                    "type": "text",
                    "text": f"This is a quote {i}",
                    "subtype": "quote"
                })
                html_items.append(f"<!-- wp:quote --><blockquote class=\"wp-block-quote\"><p>This is a quote {i}</p></blockquote><!-- /wp:quote -->")

            elif type_choice == "chat":
                # Chat style quote
                content_items.append({
                    "type": "text",
                    "text": f"John: How are you {i}?\nJane: I am good, thank you!",
                    "subtype": "chat",
                    "formatting": [
                        {"type": "bold", "start": 0, "end": 5},
                        {"type": "bold", "start": 25, "end": 29}
                    ]
                })
                html_items.append(f"<!-- wp:quote {{\"className\":\"chat\"}} --><blockquote class=\"wp-block-quote\"><p><strong>John:</strong> How are you {i}?<br><strong>Jane:</strong> I am good, thank you!</p></blockquote><!-- /wp:quote -->")

            elif type_choice == "quirky":
                # Quirky quote
                content_items.append({
                    "type": "text",
                    "text": f"This is quirky {i}",
                    "subtype": "quirky"
                })
                html_items.append(f"<!-- wp:quote {{\"className\":\"quirky\"}} --><blockquote class=\"wp-block-quote\"><p>This is quirky {i}</p></blockquote><!-- /wp:quote -->")

            elif type_choice == "formatted":
                # Formatted paragraph
                content_items.append({
                    "type": "text",
                    "text": f"Supercalifragilisticexpialidocious {i}",
                    "formatting": [
                        {"type": "bold", "start": 0, "end": 9},
                        {"type": "italic", "start": 9, "end": 34},
                        {"type": "bold", "start": 9, "end": 20}
                    ]
                })
                html_items.append(f"<!-- wp:paragraph --><p><strong>Supercali</strong><strong><em>fragilistic</em></strong><em>expialidocious {i}</em></p><!-- /wp:paragraph -->")

            elif type_choice == "mixed":
                # Mixed content type example
                content_items.append({
                    "type": "text",
                    "text": "title",
                    "subtype": "heading1"
                })
                content_items.append({
                    "type": "text",
                    "text": "paragraph"
                })
                content_items.append({
                    "type": "text",
                    "text": "biggest",
                    "subtype": "heading1"
                })
                content_items.append({
                    "type": "text",
                    "text": "bigger",
                    "subtype": "heading2"
                })
                content_items.append({
                    "type": "text",
                    "text": "quirky row 1\nquircky row 2",
                    "subtype": "quirky"
                })
                html_items.append("<!-- wp:heading {\"level\":1} --><h1 class=\"wp-block-heading\">title</h1><!-- /wp:heading -->")
                html_items.append("<!-- wp:paragraph --><p>paragraph</p><!-- /wp:paragraph -->")
                html_items.append("<!-- wp:heading {\"level\":1} --><h1 class=\"wp-block-heading\">biggest</h1><!-- /wp:heading -->")
                html_items.append("<!-- wp:heading --><h2 class=\"wp-block-heading\">bigger</h2><!-- /wp:heading -->")
                html_items.append("<!-- wp:quote {\"className\":\"quirky\"} --><blockquote class=\"wp-block-quote\"><p>quirky row 1<br>quircky row 2</p></blockquote><!-- /wp:quote -->")

        # Create the combined NPF post and HTML block
        npf_post = {
            "content": content_items,
            "layout": [],
            "trail": [],
            "version": 2
        }
        html_block = "".join(html_items)

        # Append generated data
        data["npf_posts"].append(npf_post)
        data["html_blocks"].append(html_block)

    # Write to file
    with open(file_path, "w") as f:
        json.dump(data, f)

    print(f"Generated {count} entries of diverse test data.")

# Generate the test data
generate_massive_test_data("./diverse_massive_test_data.json")
