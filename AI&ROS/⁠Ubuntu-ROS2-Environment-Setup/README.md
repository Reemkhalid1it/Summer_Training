# Task 4 — Installing Ubuntu Linux & Setting Up ROS 2

## About This Task

This task was part of the **Smart Methods** training program. The goal was simple on paper: install Ubuntu Linux, set up ROS 2, and get a Python node running through `rclpy`. In practice, it turned into a real lesson in how much small details matter when you're working in a Linux terminal for the first time.

Below is a walkthrough of how I approached it, the environment I ended up with, and the problems I ran into along the way — documented honestly so it might save someone else the same headache.

---

## 1. Setting Up the Environment

I installed Ubuntu on a virtual machine and, since I was on Ubuntu 24.04, that meant working with **ROS 2 Jazzy** rather than Humble.

The first real step after installation was making sure the shell actually knew ROS 2 existed. That means sourcing it every time you open a terminal — or better, adding it permanently to `.bashrc` so you don't have to repeat yourself:

```bash
echo "source /opt/ros/jazzy/setup.bash" >> ~/.bashrc
source ~/.bashrc
```

To confirm it actually worked, I checked the version and the active distribution:

```bash
ros2 --version
echo $ROS_DISTRO
```

If everything is set up correctly, `$ROS_DISTRO` should print `jazzy` (or `humble`, depending on your Ubuntu version). This step feels trivial, but it's the first checkpoint — if this doesn't return the right thing, nothing else downstream will work either.

---

## 2. Running My First ROS 2 Node

Once the environment was confirmed, I wrote a basic Python node using `rclpy` to make sure a node could actually initialize and log something:

```bash
python3 ~/scripts/ros2_node.py
```

This was the point where things stopped being "installation steps" and started being "actual programming problems."

---

## 3. What Actually Went Wrong (and How I Fixed It)

This is the part that took the most time — not the installation itself, but getting code to run correctly once it was inside the terminal.

### Problem 1: The terminal was silently corrupting my code

I kept getting an error I didn't expect at all:

```
NameError: name 'name' is not defined
```

At first this made no sense — my script clearly had `if __name__ == '__main__':`. After digging into it, I realized the terminal (or the way I was pasting into it) was **stripping the double underscores**, turning `__name__` into just `name`. The script wasn't broken when I wrote it — it was getting mangled on the way in.

**How I solved it:** instead of trusting copy-paste into the terminal for multi-line scripts, I encoded the script content in Base64 and decoded it directly into a file:

```bash
echo "<base64_encoded_script>" | base64 -d > script.py
```

This bypasses whatever terminal/paste behavior was corrupting the text, because the file is written as raw bytes rather than typed/pasted characters.

### Problem 2: Indentation errors from copy-pasting

Along the same lines, copying multi-line class definitions and callback functions sometimes shifted indentation — mixing tabs and spaces, or losing leading whitespace altogether — which Python is notoriously unforgiving about.

**How I solved it:** the Base64 approach fixed this too, since it writes the file exactly as encoded, with no reformatting in between. For anything I did write manually, I made sure to keep class structures and callbacks clean and consistently indented before saving.

---

## 4. Basic ROS 2 Node Template

This is the minimal node structure I used to confirm the setup was working end-to-end:

```python
import rclpy
from rclpy.node import Node

class CustomNode(Node):
    def __init__(self):
        super().__init__('custom_node')
        self.get_logger().info("ROS 2 Node initialized successfully.")

def main(args=None):
    rclpy.init(args=args)
    node = CustomNode()
    rclpy.spin(node)
    node.destroy_node()
    rclpy.shutdown()

if __name__ == '__main__':
    main()
```

Nothing fancy — just enough to prove the node initializes, logs a message, and shuts down cleanly.

---

## 5. Takeaway

The actual ROS 2 concepts weren't the hard part of this task — sourcing an environment, writing a node, publishing a log message. What actually taught me something was debugging the *terminal itself*: realizing that code can look perfectly correct on screen and still fail, because the environment it's being typed into is quietly changing it. Base64 encoding ended up being the most reliable fix, and it's a trick I'll keep using anytime I need to move code into a Linux environment without trusting copy-paste.
