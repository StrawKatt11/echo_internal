extends Control

@onready var time: Label = $Time


func _on_tree_entered() -> void:
	time.text = Stopwatch.format_time(Stopwatch.all_time)
