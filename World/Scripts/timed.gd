extends StaticBody2D

@onready var timer: Timer = $Timer


func _on_timer_timeout() -> void:
	if visible:
		hide()
		collision_layer = 0
		collision_mask = 0
	else:
		show()
		collision_layer = 1
		collision_mask = 1
