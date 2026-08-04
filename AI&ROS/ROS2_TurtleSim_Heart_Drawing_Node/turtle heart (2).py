import rclpy
from rclpy.node import Node
from geometry_msgs.msg import Twist
from turtlesim.srv import TeleportAbsolute
import math


class TurtleHeart(Node):
    def __init__(self):
        super().__init__('turtle_heart')
        self.publisher_ = self.create_publisher(Twist, '/turtle1/cmd_vel', 10)

        # Teleport the turtle to the center of the screen before drawing
        # to avoid hitting the simulator's walls
        self.teleport_client = self.create_client(TeleportAbsolute, '/turtle1/teleport_absolute')
        self.teleport_client.wait_for_service()
        req = TeleportAbsolute.Request()
        req.x = 5.5
        req.y = 5.5
        req.theta = 0.0
        self.teleport_client.call_async(req)

        self.timer_period = 0.1
        self.timer = self.create_timer(self.timer_period, self.timer_callback)

        # Each command = (linear.x, angular.z, duration in seconds)
        self.commands = [
            (0.0, 1.0, 0.87),      # turn left 50 degrees
            (1.2, 0.0, 1.20),      # straight diagonal line (right side of heart)
            (1.2, 1.35, 2.58),     # circular arc (right lobe)
            (0.0, -1.0, 2.44),     # turn right 140 degrees (between lobes)
            (1.2, 1.35, 2.58),     # circular arc (left lobe)
            (1.2, 0.0, 1.20),      # straight diagonal line back to start
        ]

        self.cmd_index = 0
        self.ticks_in_cmd = 0

    def timer_callback(self):
        if self.cmd_index >= len(self.commands):
            self.publisher_.publish(Twist())
            self.timer.cancel()
            self.get_logger().info('Heart drawing complete!')
            return

        linear, angular, duration = self.commands[self.cmd_index]
        ticks_needed = round(duration / self.timer_period)

        msg = Twist()
        msg.linear.x = linear
        msg.angular.z = angular
        self.publisher_.publish(msg)

        self.ticks_in_cmd += 1
        if self.ticks_in_cmd >= ticks_needed:
            self.cmd_index += 1
            self.ticks_in_cmd = 0


rclpy.init()
node = TurtleHeart()
rclpy.spin(node)
node.destroy_node()
rclpy.shutdown()
