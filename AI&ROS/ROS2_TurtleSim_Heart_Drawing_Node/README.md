# Turtlesim Heart Drawing (ROS2)

A Python script that uses ROS2 and `turtlesim` to draw a heart shape by publishing linear and angular velocity commands to `/turtle1/cmd_vel`.

## Result

![Turtlesim heart result](turtlesimheart.jpg)

*Illustration of the path drawn by the turtle: a diagonal line, two rounded lobes, and a diagonal line back to the starting point, forming a heart shape.*

## Requirements

- ROS2 (any supported distribution)
- `turtlesim` package
- Python 3

## How to Run

1. In one terminal, start the turtlesim simulator:
```bash
ros2 run turtlesim turtlesim_node
```

2. In a second terminal, run the script:
```bash
python3 turtle_heart.py
```

The turtle automatically teleports to the center of the screen, then draws a complete heart shape and stops on its own.

## How the Code Works

- **Teleport to center:** Before drawing, the turtle is moved to position (5.5, 5.5) using the `/turtle1/teleport_absolute` service, ensuring enough space and avoiding wall collisions.
- **Timer-based state machine:** A ROS2 timer fires every 0.1 seconds. A list of commands (`self.commands`) defines each stage of the drawing:
  - `linear.x`: forward speed
  - `angular.z`: rotational speed
  - duration in seconds for that stage
- **Drawing sequence:**
  1. Turn left 50°
  2. Move straight (diagonal line, right side of the heart)
  3. Draw a circular arc (right lobe)
  4. Turn right 140° (transition between lobes)
  5. Draw a circular arc (left lobe)
  6. Move straight (diagonal line back to the starting point)
  7. Stop automatically

The node stops itself after completing the heart exactly once — it does not loop or repeat.

## File

- `turtle heart (2).py` — main ROS2 node that draws the heart shape.
