extends Control

@onready var time: Label = $TheTime


func _on_tree_entered() -> void:
	time.text = Stopwatch.format_time(Stopwatch.all_time)
