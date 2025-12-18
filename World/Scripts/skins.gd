extends Node
@onready var animsprite: AnimatedSprite2D = $AnimatedSprite2D


var selected_skin_index = 0 # Default to the first skin
var skins = [
	Color(255,255,255,255),
	Color(255, 0, 0, 255),
	Color(0,255,0,0),
	Color(0,0,255,255),
	Color(200,150,0,255),
	Color(67,67,0,255),
	Color(66,66,66,255)
]

func set_skin(index):
	if index >= 0 and index < skins.size():
		selected_skin_index = index
		# Emit a signal for the player to listen to, if needed
		emit_signal("skin_changed")

signal skin_changed
